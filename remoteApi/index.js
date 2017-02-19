const Hapi = require('hapi');
const minLike = 0;
const maxLike = 100000;
const server = new Hapi.Server({
    cache: { engine: require('catbox-memory') }
});
const AuthBearer = require('hapi-auth-bearer-token');
server.connection({port: process.env.PORT || 8000});
server.register({
    register: require('hapi-rate-limit'),
    options: {
        userLimit: 1000,
        userCache: {
            expiresIn: 1000 * 10 // 10 secs
        }
    }
});
server.register(AuthBearer, (err) => {
    server.auth.strategy('simple', 'bearer-access-token', {
        allowQueryToken: true,              // optional, true by default
        allowMultipleHeaders: false,        // optional, false by default
        accessTokenName: 'access_token',    // optional, 'access_token' by default
        validateFunc: function (token, callback) {
            if (true) {
                return callback(null, true, { token: token }, { token: token });
            }
        }
    });

    server.route({
        method: 'GET',
        path: '/{userId}/likes',
        config: {
            auth: 'simple',
            handler: function (request, reply) {
                reply({
                    user: Object.assign({id: request.params.userId}, request.auth.credentials),
                    likes: Math.floor(Math.random() * (maxLike - minLike)) + minLike
                });
            }
        }
    });
    server.start((err) => {
        if (err) {
            throw err;
        }
        console.log(`Server running at: ${server.info.uri}`);
    });
});
