import pycurl
from io import BytesIO

class Conectar:
    #Atributos privados
    #__portal
    #__nombreBot
    #__tiempoFuera
    #__archivoCookie
    #Constructor
    def __init__( self, cPortal, cNombreBot, cTiempoFuera, cArchivoCookie ):

        self.__portal=  cPortal
        self.__nombreBot= cNombreBot
        self.__tiempoFuera= cTiempoFuera
        self.__archivoCookie= cArchivoCookie
    #//////////////////////////////////////////////////////////////////////
    def sesionCurl(self):

        sc = pycurl.Curl()

        sc.setopt(sc.COOKIEFILE,  self.__archivoCookie)
        sc.setopt(sc.COOKIEJAR,  self.__archivoCookie)
        sc.setopt(sc.TIMEOUT,  self.__tiempoFuera)
        sc.setopt(sc.USERAGENT,  self.__nombreBot)
        sc.setopt(sc.URL,  self.__portal)
        sc.setopt(sc.VERBOSE, False)
        sc.setopt(sc.SSL_VERIFYPEER, False)
        sc.setopt(sc.FOLLOWLOCATION, True)
        sc.setopt(sc.MAXREDIRS, 4)
       
        #sc.setopt(sc.HEADER, True)
    
        b = BytesIO()
        sc.setopt(sc.WRITEDATA, b)
        sc.perform()
        sc.close()

        body= b.getvalue()
    
        return body.decode('iso-8859-1')
      #//////////////////////////////////////////////////////////////////////




    
