#!/bin/bash

# title of generated documentation, default is 'Generated Documentation'
TITLE="Form Documentation"

# name to use for the default package. If not specified, uses 'default'
PACKAGES="FORM"

# name of a directory(s) to parse directory1,directory2
DIRECTORIES=

# name of filenames to parse
FILENAMES="Form.*"

# path of PHPDoc executable
PATH_PHPDOC=$PHPDOC

# where documentation will be put
TARGET=$PWD/docs

# what outputformat to use (html/pdf)
OUTPUTFORMAT=HTML

# converter to be used
CONVERTER=Smarty

# template to use
TEMPLATE=default

# parse elements marked as private
PRIVATE=off

while [ $# -gt 0 ];
do
	echo "$1"
	case $1 in
		--dry|-n) 
			TARGET="/dev/null" 
			shift
			;;
		--phpdoc) 
			PATH_PHPDOC="$2" 
			shift 2
			;;
		*) 
			echo "Unknown $1" 
			shift
			;;
	esac
done

# make documentation

"$PATH_PHPDOC" -d "$DIRECTORIES" -f "$FILENAMES" -t "$TARGET" -ti "$TITLE" -dn $PACKAGES \
-o $OUTPUTFORMAT:$CONVERTER:$TEMPLATE -pp $PRIVATE


# vim: set expandtab :
