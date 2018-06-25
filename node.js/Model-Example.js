module.exports = (sequelize, DataTypes) => {
  const OrderDetail = sequelize.define('OrderDetail', {

    OrderDetailID: {
      type: DataTypes.INTEGER,
      autoIncrement: true,
      primaryKey: true,
      field: "sdID"
    },
    OrderDetailOrderNum: {
      type: DataTypes.INTEGER,
      field: "sdOrderNum"
    },
    OrderLineNum: {
      type: DataTypes.INTEGER,
      field: "sdLineNum"
    },
    OrderDetailPartID: {
      type: DataTypes.INTEGER,
      field: "sdInventoryID"
    },
    OrderDetailPartNum: {
      type: DataTypes.INTEGER,
      field: "sdPartNum"
    },
    OrderDetailDiscount: {
      type: DataTypes.STRING,
      field: "sdDiscount"
    },
    OrderDetailUnitPrice: {
      type: DataTypes.STRING,
      field: "sdUnitPrice"
    },
    OrderDetailDiscountPrice: {
      type: DataTypes.STRING,
      field: "sdDiscountPrice"
    },
    OrderDetailLineTotal: {
      type: DataTypes.STRING,
      field: "sdLineTotal"
    },
    OrderDetailLineQty: {
      type: DataTypes.STRING,
      field: "sdLineQty"
    },
    OrderDetailDescription: {
      type: DataTypes.STRING,
      field: "sdDescription"
    },
    OrderDetailNeedBy: {
      type: DataTypes.STRING,
      field: "sdNeedBy"
    },
    OrderDetailQuoteNum: {
      type: DataTypes.STRING,
      field: "sdQuoteNum"
    },
    OrderDetailCreateDate: {
      type: DataTypes.STRING,
      field: "sdCreateDateTime"
    },
    OrderDetailPartSize: {
      type: DataTypes.INTEGER,
      field: "psSize"
    },
  }, {
    freezeTableName: true,
    timestamps: false,
    tableName: 'v_OrderDetail',
  });

  return OrderDetail;
};
