OAuth2 Server
=============

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
