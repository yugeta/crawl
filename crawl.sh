#!/bin/bash

url_list="master/url.list"

ymdhis=`date +%Y%m%d%H%M%S`

# Make-Directory
if [ -e "data" ];then
	echo "data-folder-exists"
else
	mkdir "data"
fi
if [ -e "log" ];then
	echo "log-folder-exists"
else
	mkdir "log"
fi
#mkdir "history"

cat ${url_list}|while read line
do
	#set-line-column-data
	flg=`echo ${line}|cut -d, -f 1`
	id=`echo ${line}|cut -d, -f 2`
	name=`echo ${line}|cut -d, -f 3`
	url=`echo ${line}|cut -d, -f 4`

	#check-flg
	if [ "${flg}" != "0" ];then
		continue
	fi

	#source-get
	html=`wget --output-document=/dev/null -q -O - ${url} 2>/dev/null`

	#source-write
	echo ${html} > "data/${id}.html"

	#log-write
	echo "${ymdhis},get,${id},${url}," >> "log/${id}.log"

	# view
	echo "get | ${url}"

done
