var graphql = require('graphql');
var axios = require('axios');
var Class = require('./../classes/fields/class');
module.exports = new graphql.GraphQLObjectType({
  name: 'Program',
  fields: () => ({
    id: { 
      type: graphql.GraphQLID,
      resolve: function (obj) {
        return obj.CODE
      }
    },
    name: { 
      type: graphql.GraphQLString,
      resolve: function (obj) {
        return obj.NAME
      }
    },
    classes: {
      type: new graphql.GraphQLList(Class),
      resolve: function (obj) {
        return axios.get('https://yspirit.oldcolonyymca.org/SpiritWeb/Api?FUNCTION=GET_CLASSES&PROGRAM_ID=' + obj.CODE + '&FORMAT=JSON')
          .then(function (response) {
            return response.data;
          });
      }
    }
  })
});
