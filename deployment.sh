#!/bin/bash

echo "====================================="
echo "******** SMS Server -- GoIP *********"
echo "====================================="
echo " "
echo "_____________________________________"
echo "Terminando procesos...!!"
systemctl stop httpd # Parando el apache2 si existe en el sistema
/etc/init.d/mysql stop # Parando el mysql si existe en el sistema
puerto=$(netstat -upl | grep "44444") # si esta en uso el puerto 44444
echo $puerto | grep -Eo "goipcron" # buscamo
if [ -n "$puerto" ]
then 
  echo $puerto
  echo $(killall "goipcron") # lo detenemos
else 
  echo "Nada que terminar...!!"
fi
echo "Procesos terminados...!!"
echo "..."
echo "..."
echo "..."
echo "_____________________________________"
echo "Levantando el contenedor...!!"
#echo "$(systemctl start http)" # Iniciar  (o solo tenemos que ejecutar el run ??)
id=$(docker run -p 80:80 -p 44444:44444/udp -d --cap-add=NET_ADMIN goip:v1)

echo "Corriendo el contenedor...!!"
echo "..."
echo "..."
echo "..."
echo "_____________________________________"
echo "Levantando servicios dentro del contenedor...!!"
echo "..."
echo "..."
echo "..."
echo "$(docker exec -it $id bash /etc/init.d/mysql start)"
echo "$(docker exec -it $id bash /usr/local/goip/run_goipcron start)"
echo " "
echo " "
echo "========================="
echo "Contenedor corriendo...!!"
echo "========================="
#echo "$(docker exec -it $id bash netstat -pna | grep "44444") \n"
#id_container=$(docker ps | grep -Eo "^[\b^[:alnum:]|^\"|^::]+\b")
#readarray -d : -t strarr<<<$id_container
#for i in "${ADDR[@]}";
#do
  #echo "$i"
#done
#array=[${id_container//[ ]/}]
#echo $array
#IFS=' ' read -r -a myarray <<< "$id_container"
#for index in "${id_container[@]}"
#do 
  #echo "${index}"
#done
#echo $id_container
#docker exec -it goip:v1 bash 
