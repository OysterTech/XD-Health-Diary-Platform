/**
 * @name 个人健康日记平台-JS-主页Vue
 * @author Oyster Cheung <master@xshgzs.com>
 * @since 2020-02-01
 * @version 2020-06-06
 */

var vm = new Vue({
	el: '#app',
	data() {
		return {
			nowDate: new Date(),
			nowYear: null,
			nowMonth: null,
			nowDay: null,
			calendarArray: [],
			yearOffset: 0,
			monthOffset: 0,
			hadXieDays: [], // 这个月act了的日期
			latestList: [],
			countXieWeek: [],
			todayXieList: [], // 今天act的列表
			showImageList: [], // 当前act的图片列表
			actDate: "", // 当前act列表的日期
			enumList: [],// 所有枚举
			enumTypeList: [],// 所有枚举类型
			nowEnumType: "",// 当前枚举类型
			nowEnumList: [],// 准备被选择的枚举列表
			nowCheckedEnumList: [],// 当前被选中枚举的ID
			checkedEnumList: [],// 所有被选中枚举的ID
			checkedEnumName: [],// 所有被选中枚举的名称
			chooseItemModalName: '',
			chooseItemMultiple: false,
			actDetail: {
				time: '',
				content: '',
				imageUrl: ['']
			}, // 新增活动的详细内容
		}
	},
	components: {
		'pie-chart': pieVue,
		'choose-item-modal': chooseItemModalVue
	},
	mounted() {
		this.getAllEnumType();
		this.makeCalendar();
	},
	methods: {
		getCount: function () {
			let that = this;

			let dateObj = typeof (this.nowDate) === 'object' ? this.nowDate : new Date();
			this.nowYear = dateObj.getFullYear() + this.yearOffset;
			this.nowMonth = dateObj.getMonth() + 1 + this.monthOffset;
			this.nowDay = dateObj.getDate();

			that.hadXieDays = [];
			lockScreen();

			$.ajax({
				url: window.CONFIG.apiPath + "activity/dayCount",
				data: {
					token: TOKEN,
					year: that.nowYear,
					month: that.nowMonth
				},
				dataType: "json",
				error: function (e) {
					unlockScreen();
					console.log(e);
					showModalTips("服务器错误<br>请联系技术支持并提供以下ID<br>（可截图或直接提供最后5位）<hr><p style='font-size:22px;color:#078cff'>" + e.responseJSON.tables.Session.errorRequestId);
				},
				success: function (ret) {
					if (ret.code == 200) {
						data = ret.data;

						for (i in data) {
							if (data[i]['day']) that.hadXieDays.push(data[i]['day']);
						}
					} else if (ret.tips !== '') {
						showModalTips(ret.tips);
					} else {
						showModalTips("系统错误<br>请联系技术支持并提供以下ID<br>（可截图或直接提供最后5位）<hr><p style='font-size:22px;color:#078cff'>" + ret.requestId);
					}

					that.makePieChart('monthPlace', 1, false);
					that.makePieChart('monthToy', 2, true);
					that.makePieChart('shot', 3, true);
					that.makeLineChart();
					that.makeCountLabel();
					that.makeLatestTable();

					unlockScreen();
				}
			})
		},
		makeLatestTable: function () {
			let that = this;
			lockScreen();

			$.ajax({
				url: window.CONFIG.apiPath + "statistic/getLatestList",
				data: {
					token: TOKEN,
					year: that.nowYear,
					month: that.nowMonth
				},
				dataType: "json",
				error: function (e) {
					console.log(e);
					showModalTips("服务器错误<br>请联系技术支持并提供以下ID<br>（可截图或直接提供最后5位）<hr><p style='font-size:22px;color:#078cff'>" + e.responseJSON.tables.Session.errorRequestId);

					unlockScreen();
				},
				success: function (ret) {
					if (ret.code == 200) {
						that.latestList = ret.data;
					} else if (ret.code == 404) {
					} else if (ret.tips !== '') {
						showModalTips(ret.tips);
					} else {
						showModalTips("系统错误<br>请联系技术支持并提供以下ID<br>（可截图或直接提供最后5位）<hr><p style='font-size:22px;color:#078cff'>" + ret.requestId);
					}

					unlockScreen();
				},
			})
		},
		setDateOffset(year = 0, month = 0) {
			if (this.monthOffset + month + this.nowDate.getMonth() + 1 > 12) {
				// 已经到了最后一个月，跳到下一年
				this.yearOffset += 1;
				this.monthOffset = this.nowDate.getMonth() * -1;
			} else if (this.monthOffset + month + this.nowDate.getMonth() + 1 <= 0) {
				// 已经到了一月，跳到上一年
				this.yearOffset -= 1;
				this.monthOffset = 12 - (this.nowDate.getMonth() + 1);
			} else {
				this.monthOffset += month;
			}

			this.yearOffset += year;
			this.makeCalendar();
		},
		makeCalendar() {
			this.getCount();

			// 获取当月1号的星期
			let firstDayWeek = new Date(this.nowYear + '/' + this.nowMonth + '/' + '01').getDay();

			// 当月天数
			let nowMonthTotalDay = this.getNowMonthTotalDay(this.nowYear, this.nowMonth);

			let arr = [];
			let indexEmpty, indexNum;

			// 根据当月1号的星期数来给渲染数组前面添加对应数量的空格
			for (indexEmpty = 0; indexEmpty < parseInt(firstDayWeek); indexEmpty++) {
				arr.push('');
			}

			// 通过函数判断当前月份有多少天,并根据天数填充渲染数组
			for (indexNum = 1; indexNum < nowMonthTotalDay + 1; indexNum++) {
				arr.push(indexNum);
			}

			this.calendarArray = arr;

		},
		getNowMonthTotalDay(year = 0, month = 0) {
			return [null, 31, null, 31, 30, 31, 30, 31, 31, 30, 31, 30, 31][month] || (this.isLeapYear(year) ? 29 : 28);
		},
		isLeapYear(year = 0) {
			return (year % 400 === 0 || year % 4 === 0) && year % 100 !== 0;
		},
		makePieChart(pieId = '', type = '', needUnknown = false) {
			let that = this;
			lockScreen();

			$.ajax({
				url: window.CONFIG.apiPath + "statistic/getTotalByEnum",
				data: {
					token: TOKEN,
					type: type,
					year: that.nowYear,
					month: that.nowMonth,
				},
				dataType: "json",
				error: function (e) {
					unlockScreen();
					console.log(e);
					showModalTips("服务器错误<br>请联系技术支持并提供以下ID<br>（可截图或直接提供最后5位）<hr><p style='font-size:22px;color:#078cff'>" + e.responseJSON.tables.Session.errorRequestId);
				},
				success: function (ret) {
					if (ret.code == 200 || ret.code == 404) {
						let chartData = ret.code === 200 ? (JSON.parse(JSON.stringify(ret.data.data))) : [];

						// 如果需要统计“未知”而且有数据
						if (needUnknown === true && that.hadXieDays.length > 0) {
							let unknownData = {};
							let total = 0;
							for (i in ret.data.data) {
								total += ret.data.data[i]['value'] ? parseInt(ret.data.data[i]['value']) : 0;
							}

							// 还有未知的数据
							if (that.hadXieDays.length - total > 0) {
								unknownData.name = "未知";
								unknownData['value'] = that.hadXieDays.length - total;
								chartData.push(unknownData);
							}
						}

						that.$refs['pieChart_' + pieId].chartData = chartData;
						that.$refs['pieChart_' + pieId].init();
					} else if (ret.tips !== '') {
						showModalTips(ret.tips);
					} else {
						showModalTips("系统错误<br>请联系技术支持并提供以下ID<br>（可截图或直接提供最后5位）<hr><p style='font-size:22px;color:#078cff'>" + ret.requestId);
					}

					unlockScreen();
					return;
				}
			});
		},
		makeLineChart() {
			let weekList = getNowMonthWeekList(this.nowYear, this.nowMonth);
			let actDay = [];
			let countXieWeek = [];

			for (i in this.hadXieDays) {
				actDay.push(parseInt(this.hadXieDays[i]));
			}

			// 循环每周
			for (j in weekList) {
				countXieWeek[j] = [];// 本周数据储存对象
				countXieWeek[j]['count'] = (countXieWeek[j]['count'] == undefined) ? 0 : countXieWeek[j]['count'];
				nowWeekDays = 0;// 本周总天数

				// 循环每天
				for (k in weekList[j]) {
					nowWeekDays++;

					if (actDay.indexOf(weekList[j][k]) != -1) {
						countXieWeek[j]['count']++;
					}
				}

				// 计算统计
				countXieWeek[j]['total'] = nowWeekDays - 1;
				countXieWeek[j]['percent'] = (countXieWeek[j]['count'] / countXieWeek[j]['total'] * 100).toFixed(2);
			}

			this.countXieWeek = countXieWeek;

			// 组合成图表数据
			let lineChareData = [];
			for (j in countXieWeek) {
				lineChareData.push(countXieWeek[j]['count']);
			}

			var monthCountLineChart = echarts.init(document.getElementById('monthCountLineChart'));
			var option = {
				tooltip: {
					trigger: 'axis'
				},
				xAxis: {
					type: 'category',
					data: ['1', '2', '3', '4', '5'],
					axisLine: {
						lineStyle: {
							color: "#E2E509"
						}
					},
					axisLabel: {
						formatter: '第 {value} 周'
					}
				},
				yAxis: {
					type: 'value',
					max: 6,
					axisLine: {
						lineStyle: {
							color: "#E2E509"
						}
					},
					axisLabel: {
						formatter: '{value} 次'
					}
				},
				series: [{
					name: '本周次数',
					type: 'line',
					data: lineChareData,
					markPoint: {
						data: [{
							type: 'max',
							name: '最大值'
						}]
					},
					markLine: {
						data: [{
							type: 'average',
							name: '平均值'
						}]
					}
				}]
			};
			monthCountLineChart.setOption(option);
		},
		makeCountLabel() {
			let month = this.hadXieDays.length;
			let week = this.countXieWeek;
			let dateObj = new Date();

			// 显示月次
			month = (month > 9) ? month + '' : '0' + month;
			month = month.split("");
			$("#countLabel_month").html("<span>" + month[0] + "</span><span>" + month[1] + "</span>");

			// 显示周次，先判断查看的数据是否为今日
			if (this.nowYear == dateObj.getFullYear() && this.nowMonth == dateObj.getMonth() + 1) {
				let nowWeek = Math.ceil((dateObj.getDate() + 6 - dateObj.getDay()) / 7);
				let weekCount = week[nowWeek - 1]['count'];

				// 格式化显示
				weekCount = (weekCount > 9) ? weekCount + '' : '0' + weekCount;
				weekCount = weekCount.split("");
				$("#countLabel_week").html("<span>" + weekCount[0] + "</span><span>" + weekCount[1] + "</span>");
			} else {
				$("#countLabel_week").html("<span>*</span><span>*</span>");
			}

			// 显示最长间隔
			let longestInterval = 0;
			for (i in this.hadXieDays) {
				i = parseInt(i);

				let nowDay = parseInt(this.hadXieDays[i]);
				let nextDay = (this.hadXieDays[i + 1] != undefined) ? this.hadXieDays[i + 1] : nowDay;

				// -1是要算实际的两天中间间隔
				longestInterval = ((nextDay - nowDay) - 1 > longestInterval) ? (nextDay - nowDay) - 1 : longestInterval;
			}

			// 格式化显示
			longestInterval = (longestInterval > 9) ? longestInterval + '' : '0' + longestInterval;
			longestInterval = longestInterval.split("");
			$("#countLabel_longestInterval").html("<span>" + longestInterval[0] + "</span><span>" + longestInterval[1] + "</span>");
		},
		getAllEnumType() {
			let that = this;

			$.ajax({
				url: window.CONFIG.apiPath + "enum/typeList",
				dataType: 'json',
				data: {
					token: TOKEN
				},
				error: function (e) {
					unlockScreen();
					console.log(e);
					showModalTips("服务器错误<br>请联系技术支持并提供以下ID<br>（可截图或直接提供最后5位）<hr><p style='font-size:22px;color:#078cff'>" + e.responseJSON.tables.Session.errorRequestId);
				},
				success: function (ret) {
					if (ret.code == 200) {
						for (i in ret.data['data']) {
							let info = ret.data['data'][i];

							if (info['id']) {
								let extraParam = JSON.parse(info['extra_param']);

								// 把扩展字段写入对象中
								for (j in extraParam) {
									info[j] = extraParam[j];
								}

								delete info.extra_param;
								that.enumTypeList.push(info);
							}
						}
					} else if (ret.code == 404) {
						showModalTips("无任何枚举类型");
					} else if (ret.tips !== '') {
						showModalTips(ret.tips);
					} else {
						showModalTips("系统错误<br>请联系技术支持并提供以下ID<br>（可截图或直接提供最后5位）<hr><p style='font-size:22px;color:#078cff'>" + ret.requestId);
					}

					unlockScreen();
					return;
				}
			});
		},
		getEnum: function (type = '', typeName = '', multiple = false) {
			let that = this;

			this.nowEnumType = type;
			this.nowEnumList = [];
			this.chooseItemMultiple = multiple;

			// 如果没有获取过这个类型的枚举值
			if (this.enumList[type] == undefined) {
				lockScreen();

				$.ajax({
					url: window.CONFIG.apiPath + "enum/list",
					dataType: 'json',
					data: {
						token: TOKEN,
						type: type
					},
					error: function (e) {
						unlockScreen();
						console.log(e);
						errorTips = this.url.replace(window.CONFIG.apiPath, "") + "<br>";
						errorTips += (e.responseJSON.message !== '') ? e.responseJSON.message : statusText;
						showModalTips("服务器错误<br>请联系技术支持并提供以下信息<hr><p style='font-size:17px;color:black'>" + errorTips);
					},
					success: function (ret) {
						if (ret.code == 200) {
							that.enumList[type] = [];
							that.nowEnumList = [];

							for (i in ret.data['data']) {
								let info = ret.data['data'][i];

								// 如果有实际值，存入备选节点列表和总表里
								if (info['value']) {
									that.nowEnumList[info['id']] = info['value'];
									that.enumList[type][info['id']] = info['value'];
								}

								// 检查是否有选中过
								if (that.checkedEnumList[type] !== undefined) {
									if (that.checkedEnumList[type].indexOf(info['id']) >= 0) {
										that.nowCheckedEnumList.push(info['id']);
									}
								}
							}

							that.chooseItemModalName = "请选择" + typeName;
							that.$refs.refff.show();
						} else if (ret.code == 404) {
							showModalTips("当前枚举类型没有可选值");
						} else if (ret.tips !== '') {
							showModalTips(ret.tips);
						} else {
							showModalTips("系统错误<br>请联系技术支持并提供以下ID<br>（可截图或直接提供最后5位）<hr><p style='font-size:22px;color:#078cff'>" + ret.requestId);
						}

						unlockScreen();
					}
				});
			} else {
				that.nowEnumList = that.enumList[type];

				for (let i in that.nowEnumList) {
					i = parseInt(i);

					// 检查是否已被选中过
					if (that.checkedEnumList[type] !== undefined && that.checkedEnumList[type].indexOf(i) >= 0) that.nowCheckedEnumList.push(i);
				}

				// 给已选中节点列表去重
				that.nowCheckedEnumList = (that.nowCheckedEnumList) ? that.nowCheckedEnumList.filter((item, index, self) => self.indexOf(item) === index) : that.nowCheckedEnumList;

				that.chooseItemModalName = "请选择" + typeName;
				that.$refs.refff.show();
			}
		},
		saveCheckedEnum: function () {
			let name_str = "";
			for (i in this.nowCheckedEnumList) {
				let name = this.enumList[this.nowEnumType][this.nowCheckedEnumList[i]];
				name_str += (name) ? name + "," : "";
			}

			// 将当前已选中枚举存入总表
			this.checkedEnumList[this.nowEnumType] = this.nowCheckedEnumList;
			this.checkedEnumName[this.nowEnumType] = name_str.substr(0, name_str.length - 1);
			// 清空当前已选中枚举
			this.nowCheckedEnumList = [];

			return;
		},
		showList: function (year = 0, month = 0, day = 0) {
			let that = this;
			lockScreen();

			$.ajax({
				url: window.CONFIG.apiPath + "activity/list",
				data: {
					token: TOKEN,
					year: year,
					month: month,
					day: day
				},
				dataType: "json",
				error: function (e) {
					unlockScreen();
					console.log(e);
					showModalTips("服务器错误<br>请联系技术支持并提供以下ID<br>（可截图或直接提供最后5位）<hr><p style='font-size:22px;color:#078cff'>" + e.responseJSON.tables.Session.errorRequestId);
				},
				success: function (ret) {
					if (ret.code == 200 || ret.code == 404) {
						data = ret.data;

						$("#listModalTitle").html(year + "-" + month + "-" + day + "共有" + data.length + "次");

						that.todayXieList = data;
						that.actDate = year + "-" + month + "-" + day;

						$('#listModal').modal('show');
					} else if (ret.tips !== '') {
						showModalTips(ret.tips);
					} else {
						showModalTips("系统错误<br>请联系技术支持并提供以下ID<br>（可截图或直接提供最后5位）<hr><p style='font-size:22px;color:#078cff'>" + ret.requestId);
					}

					unlockScreen();
				}
			})
		},
		showImage: function (date = '', id = 0) {
			$.ajax({
				url: window.CONFIG.apiPath + "file/list",
				data: {
					id: id
				},
				type: "post",
				dataType: "json",
				error: function (e) {
					showModalTips("图片太涩了<br>服务器加载不出来啊<hr>……服务器未知错误……");
					console.log('e', e);
				},
				success: function (ret) {
					if (ret.code == 200) {
						$("#imageListTable").html("");
						$("#imageModalTitle").html(date + "的精彩瞬间");

						imgUrl = ret.data['list'];
						for (i in imgUrl) {
							url = imgUrl[i];
							bgColor = i % 2 == 0 ? "#e5e5e5" : "white";

							showUrl = url.substr(35);
							showUrl = showUrl.substr(0, showUrl.indexOf("?"));

							html = "" +
								"<tr style='background-color:" + bgColor + "'>" +
								"<td style='text-align:left;padding-left: 10px;'>" +
								"<a target='_blank' href='" + url + "'>" + showUrl + "</a>" +
								"</td>" +
								"</tr>";

							$("#imageListTable").append(html);
						}

						$("#imageListModal").modal("show");
					} else if (ret.tips != "") {
						showModalTips(ret.tips);
					} else {
						console.log("toSee", ret);
						showModalTips("系统错误<br>请联系技术支持并提供以下ID<br>（可截图或直接提供最后5位）<hr><p style='font-size:22px;color:#078cff'>" + ret.requestId);
					}
				}
			})
		},
		goact: function () {
			this.actDetail = {
				time: '',
				content: '',
				imageUrl: ['']
			};

			$('#actModal').modal('show');
		},
		toAdd: function () {
			let that = this;
			let content = this.actDetail.content;
			let datetime = this.actDetail.time;
			let imageUrl = this.actDetail.imageUrl;

			if (content == "" || datetime == "") {
				showModalTips("别那么着急嘛<br>填好所有信息再提交哦<hr>Tips:先收拾好战场!!");
				return false;
			}

			lockScreen();

			year = datetime.substr(0, 4);
			month = datetime.substr(5, 2);
			day = datetime.substr(8, 2);
			time = datetime.substr(11);

			// 去掉月日前的0
			month = (month.substr(0, 1) == "0") ? month.substr(1) : month;
			day = (day.substr(0, 1) == "0") ? day.substr(1) : day;

			// 获取当前已选中的枚举值
			let enumList = this.checkedEnumList;
			let enumId = [];
			for (type in enumList) {
				for (i in enumList[type]) {
					if (typeof enumList[type][i] == 'string' || typeof enumList[type][i] == 'number') enumId.push(enumList[type][i]);
				}
			}

			$.ajax({
				url: window.CONFIG.apiPath + "activity/create",
				data: {
					token: TOKEN,
					year: year,
					month: month,
					day: day,
					time: time,
					content: content,
					imageUrl: imageUrl,
					enumId: enumId,
				},
				dataType: "json",
				type: "post",
				error: function (e) {
					unlockScreen();
					console.log(e);
					showModalTips("服务器错误<br>请联系技术支持并提供以下ID<br>（可截图或直接提供最后5位）<hr><p style='font-size:22px;color:#078cff'>" + e.responseJSON.tables.Session.errorRequestId);
				},
				success: function (ret) {
					unlockScreen();

					if (ret.code == 200) {
						showModalTips("爽完啦啦啦 √<br>系统已经为你记好了哦<br>期待下次的激情呢~<hr>PS.赶紧去做正事了哦！");
						$("#actModal").modal('hide');
						that.getCount();
						return true;
					} else {
						showModalTips("系统错误<br>请联系技术支持并提供以下ID<br>（可截图或直接提供最后5位）<hr><p style='font-size:22px;color:#078cff'>" + ret.requestId);
						$("#actModal").modal('hide');
						return true;
					}
				}
			});
		},
		readyDelete: function (id = 0) {
			let that = this;

			if (confirm("真的不是你act的吗？\n误删了这份激情是没办法找回的哦！")) {
				lockScreen();

				$.ajax({
					url: window.CONFIG.apiPath + "activity/delete",
					data: {
						token: TOKEN,
						id: id
					},
					dataType: "json",
					type: "delete",
					error: function (e) {
						unlockScreen();
						console.log(e);
						showModalTips("服务器错误<br>请联系技术支持并提供以下ID<br>（可截图或直接提供最后5位）<hr><p style='font-size:22px;color:#078cff'>" + e.responseJSON.tables.Session.errorRequestId);
					},
					success: function (ret) {
						unlockScreen();

						if (ret.code == 200) {
							$("#listModal").modal('hide');
							alert("删除成功")
							that.getCount();
							return true;
						} else {
							showModalTips("系统错误<br>请联系技术支持并提供以下ID<br>（可截图或直接提供最后5位）<hr><p style='font-size:22px;color:#078cff'>" + ret.requestId);
							return true;
						}
					}
				})
			} else {
				return false;
			}
		},
		toUpload: function (id = 0) {
			let that = this;
			var fileList = $("#uploadImage_" + id).get(0).files;

			if (fileList.length != 1) {
				showModalTips("每次只能上传一张照片哟~");
				return false;
			}

			if (fileList[0].size > 52428800) {
				showModalTips("一次最大只能上传50M的照片哦！");
				return false;
			}

			lockScreen();

			var formdata = new FormData();
			formdata.append('file', fileList[0]);

			$.ajax({
				url: window.CONFIG.apiPath + "file/upload",
				type: "post",
				processData: false,
				contentType: false,
				data: formdata,
				dataType: "json",
				error: function (e) {
					unlockScreen();
					console.log(e);
					showModalTips("服务器错误<br>请联系技术支持并提供以下ID<br>（可截图或直接提供最后5位）<hr><p style='font-size:22px;color:#078cff'>" + e.responseJSON.tables.Session.errorRequestId);
				},
				success: function (ret) {
					unlockScreen();

					if (ret.code == 200) {
						url = ret.data['url'];
						that.actDetail.imageUrl[id] = url;
						that.actDetail.imageUrl.push('');
						return true;
					} else if (ret.tips !== '') {
						showModalTips(ret.tips);
					} else {
						showModalTips("系统错误<br>请联系技术支持并提供以下ID<br>（可截图或直接提供最后5位）<hr><p style='font-size:22px;color:#078cff'>" + ret.requestId);
					}
				}
			})
		},
		manualInputImageUrl: function (id = 0) {
			url = prompt("请输入文件相对路径");

			if (url == null) {
				return;
			} else if (url == '' || url.length < 5) {
				showModalTips("请准确输入文件相对路径");
				return false;
			}

			this.actDetail.imageUrl[id] = url;
			this.actDetail.imageUrl.push('');

			// 修改input状态
			$("#uploadImage_" + id).attr("type", "text");
			$("#uploadImage_" + id).val(url);
			$("#uploadImage_" + id).attr("disabled", true);

			$("#manualUrl_btn_" + id).attr("disabled", true);
			//$("#manualUrl_btn_" + id).attr("class", "btn btn-danger");
			//$("#manualUrl_btn_" + id).html("修改");
		},
	}
});


function getNowMonthWeekList(year, month) {
	var oneDayLong = 24 * 60 * 60 * 1000;
	var weekList = [];

	var first = new Date(year + '/' + month + '/01');
	var firstTime = first.getTime();
	var firstWeekDate = (first.getDay() === 0) ? 6 : first.getDay() - 1;

	first.setDate(0);
	var monthHasDays = first.getDate() + firstWeekDate;
	var totalWeek = Math.ceil(monthHasDays / 7);

	for (var j = 0; j < totalWeek; j++) {
		var nowWeekDays = [];

		for (var i = 0; i < 7; i++) {
			var sundayTime = firstTime + (i - firstWeekDate - 1 + 7 * j) * oneDayLong;
			var sundayTime = new Date(sundayTime);

			var m = sundayTime.getMonth() + 1; //月
			var d = sundayTime.getDate(); //日

			if (m === month) nowWeekDays.push(d);
		}

		weekList.push(nowWeekDays);
	}

	return weekList;
}