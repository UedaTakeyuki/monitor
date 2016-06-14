logfile_name=$1
compaction_size=1000 # 切り詰めを行う閾値
compacted_size=100   # 切り詰める長さ

logfile_length=`cat $logfile_name | wc -l`

# 
if test $logfile_length -gt $compaction_size; then
	tail -n $compacted_size $logfile_name > ${logfile_name}.tmp
	rm $logfile_name
	mv ${logfile_name}.tmp $logfile_name
fi

