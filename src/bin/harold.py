#!/usr/bin/python

import time
import socket
import serial
import MySQLdb
import os

# configure the serial connections (the parameters differs on the device you are connecting to)
ser = serial.Serial(
	port='/dev/ttyUSB0',
	baudrate=4800
)

ser.open()
ser.isOpen()
ser.flushInput()

id=''

while 1:
	id = ser.readline()
	id = id[1:]
	id = id.rstrip()
	print "ibutton id read: " + id

	# TODO: check ID CRC, skip rest of loop if bad
	
	s = socket.socket(socket.AF_INET, socket.SOCK_STREAM)
	s.connect(("totoro.csh.rit.edu", 56123))
	s.send(id + "\n");
	
	print "sent data to totoro..."

	time.sleep(1)
	username = s.recv(100)
	username = username.rstrip()

	print "recvd username: " + username
	if len(username) < 1:
		print "Invalid iButton ID"
		continue

	conn = MySQLdb.connect (host = "localhost", user = "harold", passwd = "goshsurehopeidontfailthismajorproject", db = "harold")
	cursor = conn.cursor ()
	query = "SELECT selection FROM selections WHERE username='" + username + "';"
	cursor.execute(query)
	row = cursor.fetchone()
	if row == None:
		os.system("mplayer /var/www/songs/priceisright.wav");
        	os.system("killall -9 mplayer")
        	ser.flushInput()		
		continue
	song_id = row[0]
	query = "SELECT filename FROM songs WHERE id='" + str(song_id) + "';"
	cursor.execute(query)
	row = cursor.fetchone()
	if row == None:
		filename = "/var/www/songs/priceisright.wav"
	else:
		filename = row[0] 
	print "filename:", filename

	cursor.close ()
	conn.close ()

	# play song file
	# TODO: play via mpd instead of mplayer
	print "executing command: " + "mplayer \"/var/www/songs/" + username + "/" + filename + "\" "
	if username == "astebbin":
		os.system("mplayer -endpos 30 \"/var/www/songs/" + username + "/" + filename + "\" ");
	else:
		os.system("mplayer -endpos 20 \"/var/www/songs/" + username + "/" + filename + "\" ");
	ser.flushInput()
