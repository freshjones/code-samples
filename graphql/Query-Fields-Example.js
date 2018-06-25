var graphql = require('graphql');
var axios = require('axios');
var Program = require('./fields/program');
module.exports = {
  type: new graphql.GraphQLList(Program),
  resolve: function (obj) {
    return axios.get('https://yspirit.oldcolonyymca.org/SpiritWeb/Api?FUNCTION=GET_PROGRAMS&FORMAT=JSON')
      .then(function (response) {
        return response.data;
      });
  }
}
