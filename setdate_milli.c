// Set Datetime with microsec accuracy.

//http://qiita.com/kirksencho/items/eaa34ee575efe536d360
//http://kazmax.zpp.jp/cmd/g/gettimeofday.2.html

#include <stdio.h>
#include <sys/time.h>

int main(int argc, char *argv[]){
  struct timeval t;
  t.tv_sec = atoi(argv[1]);
  t.tv_usec = atoi(argv[2])*1000;

  printf ("%i.%i\n", t.tv_sec, t.tv_usec);
  settimeofday(&t, NULL);
  gettimeofday(&t, NULL);
  printf ("%i.%i\n", t.tv_sec, t.tv_usec);
}


