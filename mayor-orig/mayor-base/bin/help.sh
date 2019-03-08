#!/bin/bash

OPT_SPEC="hf::"
LONG_OPT_SPEC="help,file:,f::"
PARSED_OPTIONS=$(getopt -n "$0" -a -o $OPT_SPEC --long $LONG_OPT_SPEC -- "$@")
OPTIONS_RET=$?
eval set -- "$PARSED_OPTIONS"

help_usage() {
    print_help;
    echo -e "\nHELP használata: mayor help [parancs]\n\n"
}

if [ $OPTIONS_RET -ne 0 ] || [ $# -le 1 ]; then  help_usage; exit; fi

while [ $# -ge 1 ]; do
    case $1 in
        --help | -h )           help_usage
                                exit
                                ;;

        --file | -f )           shift
                                FILE="$1"
				echo "FILE: $FILE"
                                ;;

        -- )                    shift
				break
                                ;;

        * )                     echo "HIBA: ismeretlen opció: $1" # ide elvileg sose jutunk, mert a getopts már kiszűrte a hibás paramétereket...
                                exit
                                ;;
    esac
    shift
done

while [ $# -ge 1 ]; do
    echo -e "\n---------- HELP: $1 ----------\n"
    if [[ ! "${CMDS[*]}" =~ .*$1.* ]]; then
	echo -e "Ismeretlen parancs: $1"
	#print_help
    else
	. ./$1.sh --help
    fi
    shift
done
