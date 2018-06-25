var DataLoader = require('dataloader');
var _ = require('lodash');
var axios = require('axios');
var utilities = require('./utilities');

module.exports = function () {
  return {
    branches: Branches(),
    programs: Programs(),
    classes: Classes(),
    offerings: Offerings(),
    offering: Offering(),
    sessions: Sessions(),
  };
};

function Branches() {
  return function(id)
  {
    return axios.get('https://yspirit.oldcolonyymca.org/SpiritWeb/Api?FUNCTION=GET_BRANCHES&FORMAT=JSON')
    .then(function (res) { 
      return res.data.filter(function(obj){ 
        if(typeof obj === 'object' && typeof obj.CODE === 'string' && obj.CODE.length > 0 )
        {
          obj.CODE = parseInt(obj.CODE);
          return obj;
        }
      });
    })
    .then(function(data){
      return !id ? data : _.filter(data,{CODE:id});
    })
  }
}

function Sessions() {
  return function(id)
  {
    var url = `https://yspirit.oldcolonyymca.org/SpiritWeb/Api?FUNCTION=GET_SESSIONS&FORMAT=JSON&BRANCH_ID=${id}`;
    return axios.get(url)
      .then(function (res) { 
        return res.data 
      });
  }
}

function Programs() {
  return function(branch_id,program_id)
  {
    var query='',params = [];
    params.push('FUNCTION=GET_PROGRAMS');
    params.push('FORMAT=JSON');
    if(branch_id) params.push(`BRANCH_ID=${branch_id}`);
    if(params.length) query = '?' + params.join('&');
    var url = `https://yspirit.oldcolonyymca.org/SpiritWeb/Api${query}`;
    return axios.get(url)
      .then(function (res) {
        return res.data.filter(function(obj){ 
          if(typeof obj === 'object' && typeof obj.CODE === 'string' && obj.CODE.length > 0 )
          {
            obj.BRANCH_ID = branch_id;
            obj.CODE = parseInt(obj.CODE);
            return obj;
          }
        });
      })
      .then(function(data){
        return !program_id ? data : _.filter(data,{CODE:program_id})
      })
  }
}

function Classes() {
  return function(program_id,branch_id,class_id)
  {
    var query='',params = [];
    params.push('FUNCTION=GET_CLASSES');
    params.push('FORMAT=JSON');
    params.push(`PROGRAM_ID=${program_id}`);
    if(branch_id) params.push(`BRANCH_ID=${branch_id}`);
    if(params.length) query = '?' + params.join('&');
    var url = `https://yspirit.oldcolonyymca.org/SpiritWeb/Api${query}`;
    return axios.get(url)
    .then(function (res) { 
      return res.data.filter(function(obj){ 
        if(typeof obj === 'object' && typeof obj.CODE === 'string' && obj.CODE.length > 0 )
        {
          obj.CODE = parseInt(obj.CODE);
          return obj;
        }
      });  
    })
    .then(function (data) { 
      return data.length > 0 ? utilities.dedupe(data) : []; 
    })
    .then(function(data){
      return !class_id ? data : _.filter(data,function(obj){
        if( _.indexOf(class_id,obj.CODE) > -1  )
          return obj;
      });
    })
  }
}

function Offerings() {
  return function(args)
  {
    var query='',params = [];
    params.push('FORMAT=JSON');
    params.push('FUNCTION=GET_CLASS_OFFERING_LIST');
    if(args.branch) params.push(`BRANCH_ID=${args.branch}`);
    if(args.program) params.push(`PROGRAM_ID=${args.program}`);
    if(args.class) params.push(`CLASS_ID=${args.class}`);
    if(params.length) query = '?' + params.join('&');
    var url = `https://yspirit.oldcolonyymca.org/SpiritWeb/Api${query}`;
    return axios.get(url).then(function (res) { 
      return res.data;
    })
  }
}

function Offering() {
  return function(id)
  {
    var query='',params = [];
    params.push('FORMAT=JSON');
    params.push('FUNCTION=GET_CLASS_OFFERING');
    params.push(`CLASS_OFFERING_ID=${id}`);
    if(params.length) query = '?' + params.join('&');
    var url = `https://yspirit.oldcolonyymca.org/SpiritWeb/Api${query}`;
    return axios.get(url).then(function (res) { 
      return res.data;
    })
  }
}
