#!/bin/sh

# Receipt printing script for Citizen CT-S310 Receipt Printer.

## Instructions ##
#
# To install their driver, you must get it from here:
#
# http://www.citizen-systems.co.jp/english/support/download/printer/driver/cups/index.html
#
# If you're on 64 bit Debian based (Like Ubuntu)
#
# Download the source here and continue:
# http://www.citizen-systems.co.jp/english/support/download/printer/driver/cups/data_cups/ctzpos-cups-1.0.3-0.src.rpm
#
# Extract the sources (don't install)
# 
# Install packages: libcups2-dev libcupsimage2-dev
#
# Compile rastertocbm1k.c with this command:
# gcc -Wl,-rpath,/usr/lib -Wall -fPIC -O2 -o ./rastertocbm1k ./rastertocbm1k.c -lcupsimage -lcups
#
# Copy this file to /usr/lib/cups/filter/ with this command:
# sudo cp rastertocbm1k /usr/lib/cups/filter/
#
# Install your Citizen CT-S310 using the CTS310.ppd file.
#   ** Congratulations! You now have a receipt printer for Pines! **
#
# Now you can open the file you are given when you print a receipt with this
# script. You can set Firefox to automatically open these files with receipt.sh.

lpr -o PageSize=X72MMY30MM -o PageCutType=1EndOfDoc -o cpi=17 $1
