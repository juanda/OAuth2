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
app/console OAuth2:CreateClient consultaExpediciones 'http://localhost:3000/token' password,client_credentials,authorization_code,implicit publica,privada
app/console OAuth2:CreateUser juanda juanda

## Pedir un token

### Con flujo "client_credentials"

Suponiendo que `client_id` es consultaExpediciones
y `client_secret` es ap3117jan7cwo0gkko8o4gkg8oos48k

POST http://localhost:8000/token

headers:
Authorization: Basic Y29uc3VsdGFFeHBlZGljaW9uZXM6YXAzMTE3amFuN2N3bzBna2tvOG80Z2tnOG9vczQ4aw==

parámetros:
grant_type=client_credentials&scope=publica

Con curl se haría

curl -u consultaExpediciones:ap3117jan7cwo0gkko8o4gkg8oos48k "http://localhost:8000/token" -d 'grant_type=client_credentials&scope=publica'

Nota: El chorizaco del header es el base64(consultaExpediciones:ap3117jan7cwo0gkko8o4gkg8oos48k)

### Con flujo "password"

POST http://localhost:8000/token

headers:
Authorization: Basic Y29uc3VsdGFFeHBlZGljaW9uZXM6YXAzMTE3amFuN2N3bzBna2tvOG80Z2tnOG9vczQ4aw==

parámetros:
grant_type=password&scope=privada&username=juanda&password=juanda

Con curl se haría

curl -u consultaExpediciones:ap3117jan7cwo0gkko8o4gkg8oos48k "http://localhost:8000/token" -d 'grant_type=password&username=juanda&password=juanda&scope=publica'

Nota: El chorizaco del header es el base64(consultaExpediciones:ap3117jan7cwo0gkko8o4gkg8oos48k)



### Con flujo "authorization_code"

En un navegador:

GET http://localhost:8000/authorize?response_type=code&client_id=consultaExpediciones&scope=privada&state=xyz&redirect_uri=http://localhost:3000/token

Atención: el parámetro redirect_uri debe coincidir con el redirect_uri con que se registró el cliente.


POST http://localhost:8000/token

headers:
Authorization: Basic Y29uc3VsdGFFeHBlZGljaW9uZXM6YXAzMTE3amFuN2N3bzBna2tvOG80Z2tnOG9vczQ4aw==

parameter:
grant_type=authorization_code&code=c2ebaa8e17c362d4e4a0edd18dd6a08374c06238&client_id=consultaExpediciones&client_secret=kc57pvom71w8ck48gwc8o40wwo0w8co&scope=publica&redirect_uri=http://localhost:3000/token


## Con flujo "implicit"


Si se quiere flujo implicito cambiar en el flujo "authorization_code" response_type=code por response_type=token

Y se obtiene directamente el token.

## Token JWT

En todos los flujos anteriores si queremos que el token sea de tipo JWT, usamos la url http://localhost:8000/jwt_token 
y http://localhost:8080/jwt_authorize



# Notas sobre el protocolo OAuth2

"The interaction between the authorization server and resource server
is beyond the scope of this specification. The authorization server
may be the same server as the resource server or a separate entity.
A single authorization server may issue access tokens accepted by
multiple resource servers."

"This specification does not provide any methods for the resource
server to ensure that an access token presented to it by a given
client was issued to that client by the authorization server"


"The client accesses protected resources by presenting the access
token to the resource server. The resource server MUST validate the
access token and ensure that it has not expired and that its scope
covers the requested resource. The methods used by the resource
server to validate the access token (as well as any error responses)
are beyond the scope of this specification but generally involve an
interaction or coordination between the resource server and the
authorization server."



Respuesta dada en quora acerca de como hacer que el servidor de recursos
se "crea" el token dado por el servidodr de autorización 
(https://www.quora.com/In-OAuth-2-0-how-do-resource-servers-assert-a-token-issued-by-an-authorization-server)

"In OAuth 2.0, how do resource servers assert a token issued by an authorization server?
Given that exist n Resource Servers and a central Authorization Server, how Resource Servers know if a given token is valid? And how they get user metadata from those Tokens?

For example, how Google APIs (Resource Servers) assert token authenticity and user metadata?


These are great questions, and they're somewhat separate from each other so we'll take them in order.

First, when an RS gets handed a token by a client, how does it know if the token's any good or
 what it's good for? The OAuth 2.0 spec, RFC6749, very specifically punts on this issue in section 7:

The methods used by the resource server to validate the access token (as well as any error responses) 
are beyond the scope of this specification but generally involve an interaction or coordination between 
the resource server and the authorization server.


And this is for good reason: you've got a lot of options depending on what your deployment and application 
characteristics are. 

The easiest, and most common for small deployments, is to just look it up in the database. In many OAuth instances,
the RS and the AS are co-located and very tightly bound so they have access to the same data store. When the AS 
part of the server mints a token, it drops the token (or a hash of it) into a database along with all of the
information about the token that'll be needed to make an authorization decision. When that token comes back
in later, the RS part of the server just looks up the token value (or its hash) and plucks any other bits of 
data that it needs from that record in order to authorize or deny the request being made. This is the only
pattern that was assumed for OAuth 1.0, but we conscientiously split it up in OAuth 2.0. Why is that?

Well, what happens when, as you suggest, you've got multiple RS's out there and a single AS? Or if you've 
got something where you have an RS that can take tokens from multiple AS's? Then you need some means to communicate 
all that meta-information surrounding the token (what scopes it has, who authorized it, what client it was authorized 
for, when it expires, etc, etc) from the AS to the RS. 

The first thing you can do is use a structured token value, such as the soon-to-be-an-RFC JSON Web Token, or  JWT .
JWTs are pretty awesome constructs: it's a blob of JSON that you can sign and/or encrypt in a way that won't get mucked
up in transit. And they're really easy to create and parse properly (no normalization!) in just about any language, 
even if you don't have a library (and there are quite a few). JWTs define a set of common claims, such as issuer,
audience, subject, and other bits that you'd probably want to know about for a security object like this.
So the RS gets handed a JWT, it parses the JWT, checks the signature or decrypts it, reads the claims, sees 
who the token's for and what it's for and if it hasn't expired, and you're good to go. The RS could get
everything it needs from that.

But this assumes that you're OK with that information being packed into the token, which could make the token
rather large and unwieldy if you've got a lot to say about the authorization context. The client could also read 
what's in the token, which might leak sensitive information. OAuth tokens are opaque to clients, which means they 
don't have to read the token to use it, but that doesn't mean that a client can't *try* to read the token and get 
something useful out of it. You can combat this by encrypting the token (which assumes you've got some key management
so the RS gets the decryption key), but even the JWT specification says that the best way to avoid privacy leakage
issues is to just not put sensitive information inside the token itself. And it also assumes that you're OK with
tokens being good until they expire, because if the RS is parsing the token on its own, there's no good way to
revoke a token once it's in flight. You can combat this by having "short enough" timeouts on the tokens, but
there's another option here as well.

If the RS instead has a service that it can call at runtime to get information about the token in the context
of its authorization decision, then it can find out in real time if the token has been revoked or not. And if
it's making that call, it could also just as easily find out all of the important meta-information about that
token. One draft standard way of doing this is 
TokenIntrospection (caveat emptor: I am the editor of this specification). Token Introspection defines a very
simple HTTP service that lets an RS send the token over in a POST and get back a JSON document that says what 
the token's good for. Introspection re-uses the claims defined in JWT and adds a few of its own. Like JWT, 
it's a draft standard but it has several implementations in the wild already and has been in production usage 
for years. The RS authenticates to the AS during this call so that not just anyone can go fish for token information.

This assumes that your RS will be able to call the AS for each token that it sees, and that you're OK with
the extra network traffic. Herein lies the classic accuracy/performance tradeoff you find everywhere in 
etworked systems: you can have live information by calling the authoritative source in real time (using introspection)
or you can have self-contained information that you don't have to make a network call for (using JWT). You can, 
of course, cache the introspection call, and most implementations do this on the client side, at least to an extent.
There's always a tradeoff, and there's an old saying that's apropos here:

There are only two really hard problems in computer science: cache consistency, naming things, and off-by-one errors.

There's been some talk of having a push-based version of introspection which would help this problem a little 
(at the cost of greater complexity), but nobody's written an implementation or spec of that yet. I have a feeling
that it's going to come back around eventually once it scratches someone's itch. In the mean time, deployments of
introspection with limited cache on the RS side have proven to be pretty robust in practice.

And I need to point out here that you can of course use these two methods together. In fact, I've deployed systems 
where an RS can accept tokens from a small number of trusted AS's using this pattern: The tokens themselves are signed
JWTs, and each AS provides an introspection endpoint. The JWT contains claims about its issuer, a unique identifier,
expiry/issuance timestamp information, but nothing else. Some of the RS's in the system are fine with that: they're
just happy to get a valid token, and they can do that by checking the issuer and the signature and going on their
way. But most of the RS's in the system want to know who it was issued to (a user identifier of some flavor), 
what it's good for (a list of scopes), and if it's still good (ie, has it been revoked since it was issued but 
before it was expired). But how do they know which of the several AS's to ask? It would be bad to have them 
broadcast this token to multiple AS's just to see if it's good. So in this deployment, the RS parses the token,
checks the signature, finds the issuer of the token, and introspects the token with the AS associated with that
issuer URL. We've found that this works really well.

There are of course many other, more proprietary ways that you could do this, from backend ESBs to some type of
inter-process communication in a cluster to quantum token entanglement, and these might make sense for a given
deployment. You need to always weigh your options, and both JWTs and Introspection are both widely deployed and 
on their way to being solid, accepted standards.

As for user information: you could include it in either response, if you wanted to, but you're better off having a 
dedicated protected resource for exactly that purpose. That way you can protect it with special scopes and have a 
better chance of preserving privacy and protecting sensitive user information. One such standard way to do this is 
the UserInfo Endpoint of OpenID Connect."


Otro donde se cuenta algo sobre el mismo problema:

http://bitoftech.net/2014/09/24/decouple-owin-authorization-server-resource-server-oauth-2-0-web-api/