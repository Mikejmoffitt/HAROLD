#!/bin/sh

# flash built hex to attiny2313 fused internal oscillator with AVRISP mkII

avrdude -pattiny2313 -cavrispmkII -Pusb -Uflash:w:harold-ibutton.hex
