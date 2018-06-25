const express = require('express');
const bodyParser = require('body-parser');
const jwt = require('express-jwt');
const rsaValidation = require('auth0-api-jwt-rsa-validation');

// Set up the express app
const app = express();

app.use(bodyParser.json());
app.use(bodyParser.urlencoded({ extended: false }));

var jwtCheck = jwt({
  secret: rsaValidation(),
  algorithms: ['RS256'],
  issuer: "https://xxxxxxxx.auth0.com/",
  audience: 'https://apitest.xxxxxxx.com'
});

app.use(jwtCheck);

app.use(function (err, req, res, next) {
  if (err.name === 'UnauthorizedError') {
    res.status(401).json({message:'Missing or invalid token'});
  }
});

require('./server/routes')(app);

// Setup a default catch-all route that sends back a welcome message in JSON format.
app.use('*', function(req, res) {
  res.status(302).redirect('/');
});

module.exports = app;
