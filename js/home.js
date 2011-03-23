Ext.onReady(function() {

	function renderAction(s) {
		if(parseInt(s) < 120000) {
			return('<img src="images/enter.png" border="0">');
		}
		else {
			return('<img src="images/exit.png" border="0">');
		}
	}

	function renderDate(s) {
		var year=s.substr(0,4);
		var month=s.substr(4,2);
		var day=s.substr(6);
		return(month+'/'+day+'/'+year);
	}

	function renderTime(s) {
		if(s.length == 5) { s = '0'+s; }
		var hour=s.substr(0,2);
		var min=s.substr(2,2);
		var sec=s.substr(4);
		return(hour+':'+min+':'+sec);

	}

	var userFormPanel = new Ext.form.FormPanel({
		title: 'User Report',
		frame: true,
		buttonAlign: 'center',
		layout: 'form',
		defaultType: 'field',
		items: [
			{
				fieldLabel: 'User',
				id: 'user',
				xtype: 'combo',
				editable: false,
				triggerAction: 'all',
				mode: 'local',
				displayField: 'FullName',
				valueField: 'CardNumber',
				store: new Ext.data.JsonStore({
					autoLoad: true,
					url: 'index.php/home/getUserList',
					root: 'users',
					fields: [ 'CardNumber', {name: 'FullName', mapping: 'LN + ", " + obj.FN'}]
				})
			},
			{
				fieldLabel: 'From Date',
				xtype: 'datefield',
				id: 'fromDate'

			},
			{
				fieldLabel: 'To Date',
				xtype: 'datefield',
				id: 'toDate'
			},
			{
				fieldLabel: 'WIB Report',
				xtype: 'checkbox',
				id: 'userWIBReport'	
			}
				
		],
		buttons: [
			{ 
				text: 'Run Report',
				handler: function() {
					var user = Ext.getCmp('user').getValue();
					var fromDate = $('#fromDate').val();
					var toDate = $('#toDate').val();
					var wib;
					if($('#userWIBReport').attr('checked')) {
						wib = 1;
					}
					else {
						wib = 0;
					}
					resultsStore.baseParams = {
						user: user,
						fromDate: fromDate,
						toDate: toDate,
						wib: wib
					};
					resultsStore.reload();
				}
			}
		]
	});

	var attendanceFormPanel = new Ext.form.FormPanel({
		title: 'Attendance Report',
		frame: true,
		buttonAlign: 'center',
		layout: 'form',
		defaultType: 'field',
		items: [
			{
				fieldLabel: 'Type',
				id: 'attendanceReportType',
				xtype: 'combo',
				editable: false,
				triggerAction: 'all',
				mode: 'local',
				displayField: 'label',
				valueField: 'value',
				width: 150,
				store: new Ext.data.ArrayStore({
					fields: [ 'label', 'value'],
					data: [
						['Morning Report', 'morning'],
						['Evening Report', 'evening'],
					]
				})
			},
			{
				fieldLabel: 'Sort By',
				id: 'sortBy',
				xtype: 'combo',
				editable: false,
				triggerAction: 'all',
				mode: 'local',
				displayField: 'label',
				valueField: 'value',
				width: 100,
				store: new Ext.data.ArrayStore({
					fields: [ 'label', 'value'],
					data: [
						['Time', 'time'],
						['User', 'user'],
					]
				})
			},
			{
				fieldLabel: 'Date',
				width: 100,
				xtype: 'datefield',
				id: 'attendanceReportDate'
			},
			{
				fieldLabel: 'WIB Report',
				xtype: 'checkbox',
				id: 'attendanceWIBReport'
			}
				
		],
		buttons: [
			{ 
				text: 'Run Report',
				handler: function() {
					var type = Ext.getCmp('attendanceReportType').getValue();
					var sortBy = Ext.getCmp('sortBy').getValue();
					var date = $('#attendanceReportDate').val();
					var wib;
					if($('#attendanceWIBReport').attr('checked')) {
						wib = 1;
					}
					else {
						wib = 0;
					}
					alert(sortBy);
					resultsStore.baseParams = {
						type: type,
						sortBy: sortBy,
						fromDate: date,
						toDate: date,
						wib: wib
					};
					resultsStore.reload();
				}
			}
		]
	});

	var panelFormPanel = new Ext.form.FormPanel({
		title: 'Panel Report',
		frame: true,
		buttonAlign: 'center',
		layout: 'form',
		defaultType: 'field',
		items: [
			{
				fieldLabel: 'Panel',
				id: 'panel',
				xtype: 'combo',
				editable: false,
				triggerAction: 'all',
				mode: 'local',
				displayField: 'Name',
				valueField: 'id',
				width: 150,
				store: new Ext.data.JsonStore({
					autoLoad: true,
					url: 'index.php/home/getPanelList',
					root: 'panels',
					fields: [ {name: 'id', mapping: 'SitePanelId + "_" + obj.Point'}, 'Name']
				})
			},
			{
				fieldLabel: 'Date',
			        width: 100,
				xtype: 'datefield',
				id: 'panelDate'
			}
				
		],
		buttons: [
			{ 
				text: 'Run Report',
				handler: function() {
					var panel = Ext.getCmp('panel').getValue();
					var panelDate = $('#panelDate').val();
					resultsStore.baseParams = {
						panel: panel,
						fromDate: panelDate,
						toDate: panelDate
					};
					resultsStore.reload();
				}
			}
		]
	});

	var resultsStore = new Ext.data.JsonStore({
		url: 'index.php/home/runReport',
		fields: ['RecNum', {name: 'name', mapping: 'LN + ", " + obj.FN'},'CardNumber','panel', 'rdate', 'rtime'],
		root: 'records',
		method: 'POST',
		totalProperty: 'totalCount',
		idProperty: 'RecNum'
	});

	var resultsGrid = new Ext.grid.GridPanel({
		store: resultsStore,
		height: 385,
		loadMask: true,
		colModel: new Ext.grid.ColumnModel({
			columns: [
				{header: 'Action', dataIndex: 'rtime', width: 50, 'sortable': false, renderer: renderAction},
				{header: 'Name', dataIndex: 'name', width: 220, 'sortable': true},
				{header: 'Date', dataIndex: 'rdate', width: 100, 'sortable': true, renderer: renderDate},
				{header: 'Time', dataIndex: 'rtime', width: 80, 'sortable': true, renderer: renderTime},
				{header: 'ID', dataIndex: 'CardNumber', width: 80, 'sortable': true},
				{header: 'Panel', dataIndex: 'panel', width: 150, 'sortable': true}
			]
		}),
		tbarcfg: {
			buttonAlign: 'right'
		},
		tbar:[{
            		text:'Export Spreadsheet',
            		iconCls:'excel',
			handler: function() {
				window.location='index.php/home/export';
			}
        	}, '-', {
            		text:'Print Report',
            		iconCls:'print',
			handler: function() {
				window.open('index.php/home/printList', 'print_win', 'width=800,height=500,toolbars=no,scrollbars=yes');
			}
        	}],
		viewConfig: {
    			getRowClass: function(record, rowIndex, rp, ds){ // rp = rowParams

				var rtime = parseInt(record.get('rtime'));
				if(rtime < 120000) {
					return('greenBG');
				}
				else {
					return('redBG');
				}
				
    			}
		}
	});

	var resultsPanel = new Ext.Panel({
		region: 'center',
		frame: true,
		items: [resultsGrid]
	});

	var formsPanel = new Ext.Panel({
		region: 'west',
		width: 300,
		layout: 'accordion',
		layoutConfig: {
        		titleCollapse: true,
        		animate: true
    		},
		items: [userFormPanel,attendanceFormPanel,panelFormPanel]
	});


	var mainPanel = new Ext.Panel({
		renderTo: 'main',
		width: 1024,
		height: 400,
		layout: 'border',
		items: [formsPanel,resultsPanel]
	});

	mainPanel.show();
});
