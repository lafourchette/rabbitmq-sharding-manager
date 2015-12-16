#!/bin/bash

if [ $# -eq 0 ]
then
  echo "Usage: $0 [console-path] [rabbitmq-connection-uri] [rabbitmq-queue-template] [modulus] start|stop|restart|kill|status"
  echo "Ex: $0 ~/www/lafourchette-bo/app/console amqp://lafourchette:lafourchette@localhost:5673/tmp_vhost queue.{modulus} 10 start"
  exit -1
fi

# Oui c est degueu, mais le start ne rend pas la main.
function launch_command () {
	CMD=$1
	if [ "$ACTION" == "start" ]
	then
		nohup $CMD > nohup.out 2>&1 &
		sleep 1
	else
		$CMD
	fi
}

function process_consumer_shard() {

    for ((i=0 ; $MODULUS - $i ; i++))
    do
        local QUEUE=`php -r "echo strtr('$QUEUE_TEMPLATE', array('{modulus}' => $i));"`
        echo "$ACTION consumer on $QUEUE";
        #echo "php ${CONSOLE_PATH} lf:synchro:consume-message --${ACTION} --rabbitmq-connection-uri=${URI} --rabbitmq-queue-name=${QUEUE}"
        launch_command "php ${CONSOLE_PATH} lf:synchro:consume-message --${ACTION} --rabbitmq-connection-uri=${URI} --rabbitmq-queue-name=${QUEUE}"
    done
}

CONSOLE_PATH=$1
URI=$2
QUEUE_TEMPLATE=$3
MODULUS=$4

case $5 in
start)
   echo "Starting daemons..."
   ACTION="start"
   ;;
stop)
   echo "Stopping daemons..."
   ACTION="stop"
   ;;
restart)
   echo "Restarting daemons..."
   ACTION="restart"
   ;;
kill)
   echo "killing daemons right now !"
   ACTION="kill"
   ;;
*)
   echo "USAGE: $0 start|stop|restart|list|status [daemon-name]"
   exit 0
   ;;
esac

process_consumer_shard
