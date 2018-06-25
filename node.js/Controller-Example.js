const OrderServices = require('../services/orders')
module.exports = {
  // GET REQUEST
  get(req, res) {
    var from = null
    if(req.query.from)
      from = req.query.from
    var to = null
    if(req.query.to)
      to = req.query.to
    return OrderServices.getOrders( req.params.orderID, from, to )
      .then(orders => res.status(200).send(orders))
      .catch(function(err){
        res.status(400).send({message:err.message})
      })
  }
};
