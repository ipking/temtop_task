<?php

return [
	['首页',"index/index"],
	['基础设置', '', [
		'角色配置' => [
			['员工管理', 'user/user/index'],
			['角色管理', 'user/role/index'],
			['仓租用户管理', 'enterprise/enterprise/index'],
			['客户级别管理', 'enterprise/level/index'],
		],
		'其他配置' => [
			['打印机设置','sys/config/printerSetup'],
		],
	]],
	['仓库管理', '', [
		'仓库设置' => [
			['库区管理', 'wh/area/index'],
			['货位管理', 'wh/location/index'],
		],
		'仓库库存' => [
			['库存查询', 'wh/inventory/index'],
			['库存流水', 'wh/inventoryRecord/index'],
			['批次库存', 'wh/inventoryRo/index'],
		],
	]],
	['产品管理', '', [
		'产品管理' => [
			['产品管理', 'prd/Product/index']
		]
	]],
	['订单管理', '', [
		'入库单' => [
			['入库单', 'receipt/PurchaseReceipt/index']
		],
		'出库单' => [
			['出库单', 'order/TransitDeliveryOrder/index']
		],
	]],
];
