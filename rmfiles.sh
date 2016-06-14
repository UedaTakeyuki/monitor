# rmfiles.sh
# 最新2つを残し、後は消す
filepath=$1
extention=$2
while [ `ls ${filepath}/*.${extention} | wc -l` -gt 2 ]
do
  file=`ls ${filepath}/*.${extention} | sort -n | head -n 1`
  rm -f $file
  echo "$file deleted!"
done