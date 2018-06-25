const Sequelize = require('sequelize');
const Op = Sequelize.Op;
const Order = require('../models').Order
const OrderDetail = require('../models').OrderDetail
const OrderRelease = require('../models').OrderRelease


var OrderServices = {};

OrderServices.getOne = function(id){

  return Order
  .findOne({
    where:{ OrderNum: id },
    include: {
      model: OrderDetail,
      required: true,
      include: {
        model: OrderRelease,
        required: true
      }
    }
  })
}

OrderServices.getAll = function(from,to){
  return Order
  .findAll({
    where:{ OrderCreateDate: { [Op.gte]: from, [Op.lte]: to } },
    include: {
      model: OrderDetail,
      required: true,
      include: {
        model: OrderRelease,
        required: true
      }
    }
  })
}

OrderServices.getAllSanityCheck = function(from,to)
{
  return new Promise(function (resolve, reject) 
  {
    if(!from)
      reject(new Error('Please define a from query parameter (e.g. ?from=2015-09-25)'))
    
    if(!HelperService.isDate( from ) )
      reject(new Error('From is not a Date (e.g. ?from=2015-09-25)'))

    if(to != null && !HelperService.isDate( to ) )
      reject(new Error('To is not a Date (e.g. ?to=2015-09-25)'))

    var dates = {}
    dates.from = new Date(from).toISOString()
    
    if(to != null && to.indexOf("T") === -1 )
      to = to + 'T23:59:59'

    dates.to = to != null ? new Date(to).toISOString() : new Date().toISOString()
    
    resolve( dates )
  })
}

OrderServices.getOneSanityCheck = function(id)
{
  return new Promise(function (resolve, reject) 
  {
    if(!HelperService.isNumber( id ) )
        reject(new Error('ID must be an integer'))
    resolve( id )
  })
}

OrderServices.getOrders = function(id,from,to)
{
  if(id === 'all')
  {
    return OrderServices.getAllSanityCheck(from,to)
      .then(dates => OrderServices.getAll(dates.from,dates.to) )
  } 
  else 
  {

    return OrderServices.getOneSanityCheck(id)
      .then(id => OrderServices.getOne(id) )
  }
}

module.exports = OrderServices;
