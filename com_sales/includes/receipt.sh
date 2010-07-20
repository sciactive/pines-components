#!/bin/sh

# Receipt printing script for Citizen CT-S310 Receipt Printer.

lpr -o PageSize=X72MMY30MM -o PageCutType=1EndOfDoc -o cpi=17 $1
