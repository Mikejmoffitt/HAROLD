/*
    9-20-2011

    HAROLD iButton reader code for ATTiny2313
    
    By Drew Stebbins (astebbin)
*/

#include <avr/io.h>
#include <util/delay.h>

#define FOSC 1000000
#define BAUD 4800
#define MYUBRR FOSC/16/BAUD-1

#define READER_PIN 1

void ioinit(void);      // initializes IO

static uint8_t ONEWIRE_READ_COMMAND = 0x33;
static unsigned char hex_chars[16] = {'0', '1', '2', '3', '4', '5', '6', '7', '8', '9', 'A', 'B', 'C', 'D', 'E', 'F'};
static unsigned char serial_address[16];

// onewire notes:
// to write/read, master sends 1-15 us pulse, followed by 60 us write/read period
// basic sequence: reset pulse -> 8-bit command (eg read) -> data sent/received in 8-bit groups

void serial_write(unsigned char out_char)
{
	while ((UCSRA & _BV(UDRE)) == 0)		// while NOT ready to transmit 
	{;;} 
	UDR = out_char;
}

void serial_write_string(unsigned char* out_string)
{
	while(*out_string != '\0')
	{
		serial_write(*out_string);
		out_string++;
	}
}

void set_pin_for_input() 
{
	//DDRB = (DDRB & ~_BV(READER_PIN)); //PB4 = MISO, PB1 = iButton reader pin
	DDRB = 0xED;
}

void set_pin_for_output() 
{
	//DDRB = (DDRB | _BV(READER_PIN)); //PB4 = MISO, PB1 = iButton reader pin
	DDRB = 0xEF;
}

void pull_low()
{
	PORTB &= ~_BV(READER_PIN);
}

void push_high()
{
	PORTB |= _BV(READER_PIN);
}

int pin_high()
{
	return !!(PINB & _BV(READER_PIN));
}

void write_bit(uint8_t b)
{
	set_pin_for_output();
	pull_low();
	_delay_us(5);
	if(!b)
	{
		_delay_us(50);
	}
	push_high();
	_delay_us(5);
	if(b)
	{
		_delay_us(50);
	}
}

int find_ibutton() 
{
	int ibutton_found = 0; 	

	set_pin_for_output();
	pull_low();

	// wait for 480 us to reset all devices
	_delay_us(480);

	// put wire high so that devices, if they exist, can pull it low to indicate presence
	push_high();
	set_pin_for_input();
	
	if (!pin_high())
	{
		return 0; // too soon, we're being shorted
		serial_print_string("shorted!");
	}

	// wait long enough for device to pull wire low
	_delay_us(60);

	// read pin; if low, device is present, not present otherwise
	ibutton_found = !pin_high();

	// wait remainder of reset/search duration, with wire high
	_delay_us(480);

	return ibutton_found;
}

void read_ibutton_serial() 
{
	// send read ROM command
	set_pin_for_output();
	int i;

	for(i = 0; i<8; i++)
	{
		write_bit(ONEWIRE_READ_COMMAND & (1 << i));
			
	}

	// read serial number from ibutton (8b CRC + 48b serial address + 8b device type identifier)
	for(i = 0; i < 16; i++) 
	{
		serial_address[i] = 0;
		int k = 0;
		
		for(k = 0; k < 4; k++)
		{
			set_pin_for_output();

			// pull wire low for 1-15 us
			PORTB &= ~_BV(READER_PIN);
			_delay_us(5);

			// put wire high so device can pull it down
			PORTB |= _BV(READER_PIN);
			set_pin_for_input();

			// wait for device to pull down wire (or not)
			_delay_us(5);

			// read bit
			char c = 0;
			c = !(PINB & _BV(READER_PIN)); // c is 1 if 0 read, 0 otherwise

			// wait for device to release line
			_delay_us(50);

			set_pin_for_output();

			// pull wire high for recovery period
			PORTB |= _BV(READER_PIN);
			_delay_us(15);

			// bit is zeroed to begin with
			if(!c) 
			{
				serial_address[i] |= (1 << k);
			}
		}

		if(serial_address[i] < 16)
		{
			serial_address[i] = hex_chars[serial_address[i]];
		}
		else
		{
			serial_address[i] = 'X';
		}
	}
}

int main (void)
{
    ioinit(); //Setup IO pins and defaults

    while(1)
    {

		if(find_ibutton())
		{
			read_ibutton_serial();			
			
			serial_write('#');			
			int k = 0;
			for(k = 0; k < 16; k++)
			{
				serial_write(serial_address[15-k]);
			}
			serial_write_string("\r\n");
		}
	
		// Toggle indicator LED
		// TODO: get rid of this for improved reader speed
		
		PORTB |= _BV(0);
		_delay_ms(50);

		PORTB &= ~_BV(0);
		_delay_ms(50);
    }
   
    return(0);
}

void ioinit (void)
{
    //1 = output, 0 = input
    DDRB = 0xED; //PB4 = MISO, PB1 = iButton reader pin 
    DDRD = 0xFE; //PORTD (RX on PD0)

    //USART Baud rate: 4800
    UBRRH = MYUBRR >> 8;
    UBRRL = MYUBRR;
    UCSRB = (1<<RXEN)|(1<<TXEN);
    UCSRC = (1<<UCSZ1)|(1<<UCSZ0);
}
