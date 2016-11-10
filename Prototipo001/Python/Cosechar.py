from tidylib import tidy_document, release_tidy_doc
from Conectar import Conectar
import time 
import re

class Recolector:
    #Atributos privados
    #__retarda
    #__nombreBot
    #__tiempoFuera
    #__archivoCookie
    def __init__(self, rNombreBot, rRetardar):
        
        self.__retardar= rRetardar
        self.__direccion=''
#---------------Atributos Conectar-------------
        self.__tiempoFuera= 60 #Un minuto
        self.__nombreBot= rNombreBot
        self.__archivoCookie= 'c:\cookie'+rNombreBot+'.txt'
#///////////////////////////////////////////////////////////////////
    def moverUnNivelAtras(self, dominio, fuente):
        
        pBarra= dominio.rfind('/')# emula strrpos php
        paginaRaiz= dominio[0:pBarra]
        fuente= fuente[3: 3+(len(fuente)-3)]# emula substr php ojo >> -3
        lista= [paginaRaiz, fuente]# emula arreglo php
        
        return lista#OJO recuerda que devuelve dos variables
#/OJO INSTRUCCIONES ELIMINADAS///////////////////////////////////////PHP
    def obtenerDominio(self, dominio):
        base= ''
        for puntero in range(8, len(dominio)):#ojo len(dominio) -1 ??
    
            if dominio[puntero:puntero+1] == '/':
                base= dominio[0:puntero]
                break
            
        return base
#///////////////////////////////////////////////////////////////////
    def resolverCompletos(self, directorio, suiche):

        if suiche == 0:
            
            if directorio[0:4] == 'http':
                self.__direccion= directorio
#///////////////////////////////////////////////////////////////////
    def resolverRaiz(self, directorio, dominio, suiche):

        if suiche == 0:
            
            if directorio[0:1] == '/':
                
                directorio= directorio[1: 1+(len(directorio)-1)]
                self.__direccion= dominio+directorio
                suiche= 1

        return suiche
#///////////////////////////////////////////////////////////////////
    def resolverSuperior(self, directorio, dominio, suiche):

        direccionDesorden= None
        
        if suiche == 0:
            if directorio[0:3]== '../':
                dominio= dominio.strip()#emula trim
                pBarra= dominio.rfind('/')#ultima barra
                #remover barra si esta al final del dominio
                if pBarra == len(dominio)-1:
                    dominio= dominio[0:len(dominio)-1]
                    pBarra= dominio.rfind('/')
                if pBarra < 8:
                    direccionDesorden= dominio
                noSalir= True
                while noSalir:
                    dominio, directorio= self.moverUnNivelAtras(dominio, directorio)
                    if directorio[0:3] != '../':
                        noSalir= False
                if direccionDesorden:
                    
                     self.__direccion= direccionDesorden+"/"+directorio
                else:
                     self.__direccion= dominio+"/"+directorio
                     
                suiche= 1
                
        return suiche
#/OJO INSTRUCCIONES ELIMINADAS///////////////////////////////////////PHP
    def resolverDominio(self, directorio, dominio, suiche):

        if suiche == 0:
            if directorio[0:1] == '/':
                base= self.obtenerDominio(dominio)
                self.__direccion= base+directorio
                suiche= 1
                
        return suiche
#/resolverDireccion/////////////////////////////////////////////////
    def repararDireccion( self, directorio, dominio ):
        directorio=  directorio.strip()
        dominio= dominio.strip()
        dominio= dominio.strip()# ?
        if (dominio.rfind('/')+1) != len(dominio):
            dominio= dominio+'/'
        directorio= directorio.replace(';', '')#emula str_replace
        directorio= directorio.replace('\\', '')
        directorio= directorio.replace('\'', '')
        #une dominio y directorio
        self.__direccion= dominio+directorio
        self.__direccion=  self.__direccion.replace('/./', '/')
        
        suiche= 0
#-------------------------------------------------------------------
        suiche= self.resolverDominio(directorio, dominio, suiche)
#-------------------------------------------------------------------
        suiche= self.resolverSuperior(directorio, dominio, suiche)
#-------------------------------------------------------------------
        suiche= self.resolverRaiz(directorio, dominio, suiche)
#-------------------------------------------------------------------
        self.resolverCompletos(directorio, suiche)
#-------------------------------------------------------------------
        #aderir protocolo
        if self.__direccion[0:7] != 'http://' and self.__direccion[0:8] != 'https://':
            self.__direccion= 'http://'+self.__direccion

        return self.__direccion
#///////////////////////////////////////////////////////////////////
    def tidy_html(self, etiqueta):# necesario instalar
        
        configurar={'uppercase-attributes':True,
                    'wrap'                 :800,}
        reparado, errores = tidy_document(etiqueta, configurar, True)
        release_tidy_doc()
            
        return reparado
#///////////////////////////////////////////////////////////////////
    def obtenerAtributo(self, etiqueta):
    
        limpiarHtml= self.tidy_html(etiqueta)
        limpiarHtml= limpiarHtml.replace('\r', '')
        limpiarHtml= limpiarHtml.replace('\n', '')
        #for i in range(0, len(limpiarHtml)):# ! es necesario ?
        data= re.findall('href=\"(.+?)\"', limpiarHtml, re.I)#re.I mayusculas y minusculas
        #for j in range(0, ):
        limpiar= data[0]
        limpiar= limpiar.replace('HREF', '')
        limpiar= limpiar.replace('href', '')
        limpiar= limpiar.replace('=', '')
        limpiar= limpiar.replace('\"', '')
       
        return limpiar
#///////////////////////////////////////////////////////////////////
    def obtenerRaiz(self, url):

        pBarra= url.rfind('/')
        if pBarra > 8:
            base= url[0:pBarra+1]#incluso la barra
        else:
            base= url
            if pBarra != len(url):
                base= base+'/'
        #si base termina con \\
        ini= len(base)-2
        fin= ini + 2
        dosBarras= base[ini:fin]
        if dosBarras == '//':
            print('entro')
            base= base[0:len(base)-1]
        return base
#///////////////////////////////////////////////////////////////////
    def cosechar(self, semilla):
        
        #instancia sesion cURL
        estaSesion= Conectar(semilla,                self.__nombreBot,
                             self.__tiempoFuera, self.__archivoCookie)
        dominio= self.obtenerRaiz(semilla)#obtener pagina base
        time.sleep(self.__retardar)#retardar en segundos la ejecucion
#--------------------------------------------------------------------        
        pagina= estaSesion.sesionCurl()#SESION CURL descargar pagina
#--------------------------------------------------------------------
        etiqueta= re.findall('<a(.+?)>',pagina, re.I)

        listaEnlace= []#declarar lista de direcciones
         
        for index in range(0, len(etiqueta)):
            atributo= self.obtenerAtributo(etiqueta[index])
            direccion= self.repararDireccion(atributo, dominio)
            listaEnlace.append(direccion)
        

        return listaEnlace
#///////////////////////////////////////////////////////////////////
