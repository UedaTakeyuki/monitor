#!/bin/bash

filepath=$1
extention=$2
#cmd=$3
num=$3


#if [ ${cmd} = "mv" ]; then
	# 年月日で保存フォルダを（なければ）つくる
#	now=`date +%Y%m%d%H%M%S`
#	folder_name=${now:0:8}
#	if [ -e ${filepath}/${folder_name} ]; then
#  	:
#	else
#  	mkdir ${filepath}/${folder_name}
#	fi
#fi

#while [ `ls ${filepath}/*.${extention} | wc -l` -gt 2 ]
while [ `ls ${filepath}/*.${extention} | wc -l` -gt ${num} ]
do
  file=`ls ${filepath}/*.${extention} | sort -n | head -n 1`
#  if [ ${cmd} = "mv" ]; then
#	  echo "$file moved!"
#		mv $file ${filepath}/${folder_name}
#  else
  	rm -f $file
  	echo "$file deleted!"
#	fi
done