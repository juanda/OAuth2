OAuth2 Server
=============

ATENCIÓN: Para que funcionen los token AWT es necesario aumentar la longitud de la
cadena del atributo token en la tabla oauth_access_token a 255 al menos.

Esta aplicación es la implementación de un servidor de autorización OAuth2.

La aplicación es muy sencilla, modifica levemente el bundle 
[oauth2-server-bundle](https://github.com/bshaffer/oauth2-server-bundle), para 
que acepte la generación de tokens JWT. Este bundle está construido sobre la
librería [oauth2-server-php](http://bshaffer.github.io/oauth2-server-php-docs/).

La modificación consiste en:

1. Crear la clase ``AppBundle\Storage\JwtStorage`` que implementa la interfaz
   ``\OAuth2\Storage\PublicKeyInterface``. Lo que significa que devuelve las 
   claves privada y publica que se usarán para firmar y verificar respectivamente.

2. Crear una acción ``/jwt_token``, que crea un servidor OAuth2, le añade 
   el ``AppBundle\Storage\JwtStorage`` y le habilita la creación de tokens JWT.

Y ya está, lo demás lo hace el bundle ``oauth2-server-bundle``


En el controlador ``DefaultController``  se ha añadido la acción ``/api`` para
mostrar como se puede implementar el servidor de recursos usando funciones del
bundle ``oauth2-server-bundle`` y sin usarlas (a pelo).

> En principio la implementación del servidor de recursos se debe poder portar
> a cualquier lenguaje sin problemas.


app/console OAuth2:CreateScope publica "Acceso sin necesidad de autentificar al usuario"
app/console OAuth2:CreateScope privada "Acceso con autentificación del usuario"
app/console OAuth2:CreateClient consultaExpediciones 'http://localhost:3000/token' password,client_credentials,authorization_code publica,privada
app/console OAuth2:CreateUser juanda juanda

## Pedir un token

### Con flujo "client_credentials"

POST http://localhost:8000/token

parámetros:

grant_type=client_credentials&client_id=consultaExpediciones&client_secret=7q5brdm4n2kos4wc0o8kgsoss8scs4c&scope=publica

### Con flujo "authorization_code"

En un navegador:

GET http://localhost:8000/authorize?response_type=code&client_id=consultaExpediciones&scope=privada&state=xyz&redirect_uri=http://localhost:3000/token

Atención: el parámetro redirect_uri debe coincidir con el redirect_uri con que se registró el cliente.


POST http://localhost:8000/token

grant_type=authorization_code&code=c2ebaa8e17c362d4e4a0edd18dd6a08374c06238&client_id=consultaExpediciones&client_secret=kc57pvom71w8ck48gwc8o40wwo0w8co&scope=publica&redirect_uri=http://localhost:3000/token

### Con flujo "password"

POST http://localhost:8000/token

parámetros:

grant_type=password&client_id=consultaExpediciones&client_secret=7q5brdm4n2kos4wc0o8kgsoss8scs4c&scope=privada&username=juanda&password=juanda

Si queremos que el token sea de tipo JWT, usamos la url http://localhost:8000/jwt_token