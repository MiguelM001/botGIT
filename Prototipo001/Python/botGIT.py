'''
Nota   threading  es  propicio  para   un  solo  procesador
el  GIL  limita  mucho   el   rendimiento   de    ejecucion
se  espera  usar  en  el  futuro  el Modulo multiprocessing
que usa dos procesadores a la vez que mejora el rendimiento

pagina=> https://docs.python.org/2/library/multiprocessing.html
'''
#NOTA el numero de hilos se ve limitado por la cantidad de portales

#NOTA los threads sin demonios terminan cuando concluye el maintreading
#que es el hilo principal para mantener autonomia en un thread hay que
#declararlo como demonio y aca es posible matarlo 

from Cosechar import Recolector
import threading
import logging# depurar programas con threads

logging.basicConfig( level=logging.DEBUG,         
format='[%(levelname)s] - %(threadName)-10s : %(message)s')

def worker(p):
   
    logging.debug('Lanzado')
    for i in range(0, len(portal)):
        #for j in range(0,len(portal[i])):
        if portal[i][1] == 0:
            portal[i][1]= 1
            urls= p.cosechar(portal[i][0])#cada portal
            if urls:
                 print('Portal ',  portal[i][0], ' Num Pag ',
                      len(urls), ' Estado ',  portal[i][1])
                 for index in range(0, len(urls)):
                     print(urls[index])
            else:
                 print('ERROR! conexion fallida')
         #else:
            #print(portal[i][1],' \n\nERROR!')
    logging.debug('Deteniendo')              
    return
    
threads = list()

portal= [['http://www.schrenk.com',0],
         ['http://gonzolytics.com',0],
         ['http://www.schrenk.com/contact.php',0]]#global

nickBot= 'Mozilla/5.0 (Windows; U; Windows N  T 5.1; en-US; rv:2.0) Treco/20110515 Fireweb Navigator/2.4'
p= Recolector(nickBot, 0)#cuidado con el tiempo de espera

suma=0
for i in range(2):#dos aranias
    suma= 1 + i
    t = threading.Thread(target=worker, args=(p, ), name= suma)#instancia 
    threads.append(t)
    #t.setDaemon(True) #lanzar un demonio
    #t.isAlive() saber si esta vivo devuelve true o false 
    t.start()


'''
for i in range(0, len(portal)):
        for j in range(0,len(portal[i])):
            print(portal[i][j])
'''
