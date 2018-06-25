(function($) {

  'use strict';

  angular.module("timeoffCal", [
    'ui.router',
    'moment-picker',
    'oi.select',
    'templates',
    'common.services.data',
    'common.services.events',
    'common.services.user',
    'common.services.calendar',
    'common.services.authenticate',
    'directives.events',
  ])
  .config(function($stateProvider, $urlRouterProvider, $locationProvider) 
  { 
    var thisMonth = moment();

    var initMonth = parseInt(thisMonth.format('M'));
    var initYear = parseInt(thisMonth.format('YYYY'));

    $urlRouterProvider.otherwise("/calendar/week");

    $locationProvider.html5Mode(true);

    $stateProvider
      
      .state({
        name:'root',
        url:'/',
        abstract:true,
        template:'<div ui-view></div>',
        controller:function($scope, resolvedUser, UserData, UserService)
        { 
          UserService.setDepartments(UserData.departments);
          UserService.setUsers(UserData.users);
          UserService.setReasons(UserData.reasons);
        },
        resolve:
        {
          
          resolvedUser : ['AuthService', function(AuthService) 
              { 
                  return AuthService.isAuthenticated().then(function(data)
                  {
                    return data;
                  });
                  
              }],
          UserData: ['$rootScope','DataService','resolvedUser', function($rootScope,DataService, resolvedUser) 
              {    
                if($rootScope.activeDomain === null)
                {
                  //set a sane default
                  $rootScope.activeDomain = 'chroma.com';
                  
                  //replace with the users domain
                  if(resolvedUser.domain !== undefined)
                    $rootScope.activeDomain = resolvedUser.domain;
                }

            return DataService.getApiData('/api/user/list?domain=' + $rootScope.activeDomain).then(function(data)
            {
              return data;
            });

              }]
        }

      })
      .state({
        name:'error',
        params : {
          data: null,
        },
        templateUrl: 'common/templates/error.tpl.html',
        controller:function($scope,$rootScope,$stateParams)
              {
                $rootScope.loading = false;

                $scope.messages = $stateParams.data.messages;
                $scope.domain = $stateParams.data.domain;
                $scope.user = $stateParams.data.user;

              }
      })
      .state({
        name:'calendar',
        parent:'root',
        abstract:true,
        url:'calendar?departments&reasons&users',
        params : {
          month: initMonth,
          year: initYear,
          departments: null,
          reasons: null,
          users: null
        },
        views:{
          
          '' : {

            templateUrl: 'common/templates/main.tpl.html',
            controller:function($state, $stateParams, $scope,$rootScope,EventsData, HolidayData, EventsService, CalendarService, UserService)
                  {

                    //$scope.activeDomain = $rootScope.domain;

                    $rootScope.loading = false;

                    if($stateParams.departments !== null)
                    {
                      var deptKeys = $stateParams.departments.split(',');
                      UserService.setActiveDepartments(deptKeys);
                    }

                    if($stateParams.reasons !== null)
                    {
                      var reasonKeys = $stateParams.reasons.split(',');
                      UserService.setActiveReasons(reasonKeys);
                    }

                    if($stateParams.users !== null)
                    {
                      var userKeys = $stateParams.users.split(',');
                      UserService.setActiveUsers(userKeys);
                    }

                    $rootScope.loading = false;

                    EventsService.setEvents(EventsData);
                    EventsService.setHolidays(HolidayData);

                    $scope.thisMonth = thisMonth;

                    $scope.selectedMonth = thisMonth.clone();
                    
                    if( $state.params.month !== null && $state.params.year !== null)
                    {
                      if($state.params.month > 0 && $state.params.month <= 12)
                      {
                        $scope.selectedMonth = moment().year($state.params.year).month($state.params.month - 1);
                      }
                    }

                    $scope.isThisMonth = false;

                    if( $scope.thisMonth.isSame($scope.selectedMonth, 'month'))
                      $scope.isThisMonth = true;

                    CalendarService.setMonthlyCalendar($scope.selectedMonth);

                    $scope.weeks = CalendarService.getMonthlyCalendar();

                    $scope.goDay = function()
                    {
                      var params = {};
                      params.day = null;

                      if($state.params.day)
                      {
                        params.day = $state.params.day;
                      } 
                      else if ($state.params.week )
                      {
                        //if we have a week defined lets get the start of the week and apply the day
                        var weekObj = CalendarService.getWeek($state.params.week);
                        params.day = weekObj.days[0].number;
                      }
                      
                      $state.go('day',params);

                    };

                    $scope.goWeek = function()
                    {
                      var params = {};

                      params.week = null;

                      if($state.params.day)
                      {

                        var thisDate = $state.params.year + '-' + $state.params.month + '-' + $state.params.day;
                        var thisMoment = moment(thisDate, "YYYY-MM-DD");
                        
                        params.day = $state.params.day;
                        params.week = CalendarService.getWeekOfMonth(thisMoment);

                      }

                      $state.go('week',params);

                    };

                    $scope.goMonth = function()
                    {
                      
                      var params = {};
                      
                      $state.go('month',$state.params);

                    };

                    $scope.refresh = function()
                    {
                      $rootScope.loading = true;
                      $state.go('.',{},{reload:'calendar'});
                    };
                    
                    $scope.dayClass = function(day)
                    {
                      var classString = 'bg-white';

                      if(day.isToday)
                      {
                        classString = 'bg-ltyellow';
                      }

                      if(!day.isCurrentMonth)
                      {
                        classString = 'bg-ltsilver';
                      }

                      return classString;

                    };

                    $scope.select = function(value)
                    {

                      if(!value.isCurrentMonth)
                  $rootScope.loading = true;

                var thisDate = value.date;

                      var params = $stateParams;
                      params.year = parseInt(thisDate.format('YYYY'));
                      params.month = parseInt(thisDate.format('M'));
                      params.day = parseInt(thisDate.format('D'));

                      $state.go('day',params);
                    };


            },
            resolve:
                {

                  EventsData: ['$rootScope','$stateParams','DataService', function($rootScope,$stateParams,DataService) 
                  {    
                      
                      var url = '/api/event/list';

                    if($stateParams.month !== null && $stateParams.year !== null)
                      {
                        if($stateParams.month > 0 && $stateParams.month <= 12)
                        {
                          url += '/' + $stateParams.month + '/' + $stateParams.year;
                        }
                      }

                    return DataService.getApiData(url + '?domain=' + $rootScope.activeDomain).then(function(data)
                    {
                      return data;
                    });
                   
                  }],
                  HolidayData: ['$stateParams','DataService', function($stateParams,DataService) 
                  { 

                    if($stateParams.year === null)
                      return [];

                    var url = '/api/holiday/list' + '/' + $stateParams.year;

                    return DataService.getApiData(url).then(function(data)
                    {
                      return data;
                    });

                  }]

                }

          },
          'navigation@calendar' : 
          {

                  templateUrl: 'common/templates/navigation.tpl.html',
                  
                  controller: function($state,$scope,$rootScope,$stateParams) {


                    $scope.activeView = 'month';

                    $scope.month = $scope.selectedMonth;
                    $scope.monthfmt = $scope.month.format("MMMM, YYYY");

                    $scope.setMonthYear = function(newValue, oldValue)
                    {
                
                $rootScope.loading = true;
                
                var params = $stateParams;
                params.month = parseInt( newValue.format('M') );
                params.year = parseInt( newValue.format('YYYY') );
                params.week = null;
                params.day = null;

                      $state.go('month',params);

                    };

                    $scope.today = function()
                    {
                      if(!$scope.isThisMonth)
                  $rootScope.loading = true;

                      $state.go('month',{month:null,year:null,week:null,day:null});
                    };

                    $scope.next = function()
                    {
                      $rootScope.loading = true;
                      var selectedMoment = $scope.selectedMonth.clone();
                      var nextMoment = selectedMoment.add(1, 'month');
                      var nextParams = {}
                      nextParams.month = nextMoment.format('M');
                      nextParams.year = nextMoment.format('YYYY');
                      nextParams.week = null;
                      nextParams.day = null;

                      $state.go('month',nextParams);
                    };

                    $scope.prev = function()
                    {
                      $rootScope.loading = true;
                      var selectedMoment = $scope.selectedMonth.clone();
                      var prevMoment = selectedMoment.subtract(1, 'month');
                      var prevParams = {}
                      prevParams.month = prevMoment.format('M');
                      prevParams.year = prevMoment.format('YYYY');
                      prevParams.week = null;
                      prevParams.day = null;
                      $state.go('month',prevParams);
                    };

                  }

              },
          'main@calendar' : 
          {
            templateUrl: 'common/templates/calendar.tpl.html',
            controller: function($rootScope,$scope,$state,$stateParams){}
          },
          'menu@calendar' : 
          {
            templateUrl: 'common/templates/menu.tpl.html',
            controller:function($scope,$state,$rootScope,$timeout,UserService)
            {
              $scope.users = UserService.getAllUsers();
              $scope.departments = UserService.getDepartments();
              $scope.reasons = UserService.getReasons();
              $scope.selectedDomain = $rootScope.activeDomain;
              $scope.showFilterDepartment = false;
              $scope.showFilterReason = false;
              $scope.showFilterUser = false;
              $scope.domains = [
                {'name':'Chroma','domain':'chroma.com'},
                {'name':'89 North','domain':'89north.com'}
              ];
              $scope.showDepartments = function()
              {
                if(typeof $scope.departments != 'object')
                  return false;
                var length = Object.keys($scope.departments).length;
                return length > 1 ? true : false;
              };
              $scope.showReasons = function()
              {
                return true;
              };
              $scope.showUsers = function()
              {
                return true;
              };
              $scope.changeDomain = function()
              {
                $rootScope.loading = true;
                $rootScope.activeDomain = $scope.selectedDomain;
                $state.go('.',{},{reload:true});
              };
              $scope.toggleAllDepartments = function(val)
              {
                angular.forEach($scope.departments, function(item) 
                {
                    if(val === 'on')
                    {
                      item.active = true;
                    } else {
                      item.active = false;
                    }
                });
                var params = $state.params;
                params.departments = null;
                $state.go($state.current.name,params);
              };
              $scope.toggleAllReasons= function(val)
              {
                angular.forEach($scope.reasons, function(item) 
                {
                    if(val === 'on')
                    {
                      item.active = true;
                    } else {
                      item.active = false;
                    }
                });
                var params = $state.params;
                params.reasons = null;
                $state.go($state.current.name,params);
              };
              $scope.toggleAllUsers= function(val)
              {
                angular.forEach($scope.users, function(item) 
                {
                    if(val === 'on')
                    {
                      item.active = true;
                    } else {
                      item.active = false;
                    }
                });
                var params = $state.params;
                params.users = null;
                $state.go($state.current.name,params);
              };
              $scope.updateActiveDepartments = function()
              {
                var params = $state.params;
                params.departments = null;
                var activeItems = [];
                var numDepts = 0;
                angular.forEach($scope.departments, function(item) {
                  if(item.active)
                  {
                    activeItems.push(item.key);
                  }
                  numDepts+=1;
                });
                if(activeItems.length > 0 && activeItems.length < numDepts)
                {
                  params.departments = activeItems.join();
                } 
                $state.go($state.current.name,params);
              };
              $scope.updateActiveReasons = function()
              {
                var params = $state.params;
                params.reasons = null;
                var activeItems = [];
                var numReasons = 0;
                angular.forEach($scope.reasons, function(item) {
                  if(item.active)
                  {
                    activeItems.push(item.key);
                  }
                  numReasons+=1;
                });
                if(activeItems.length > 0 && activeItems.length < numReasons)
                {
                  params.reasons = activeItems.join();
                } 
                $state.go($state.current.name,params);
              };
              $scope.updateActiveUser = function(user)
              {
                if(!user.active)
                {
                  user.active = true;
                } else {
                  user.active = !user.active;
                }
                $scope.updateActiveUsers();
              }
              $scope.userIsChecked = function(user)
              {
                if(!$state.params.users)
                  return;
                return user.active;
              }
              $scope.updateActiveUser = function(user)
              {
                if(!user.active)
                {
                  user.active = true;
                } else {
                  user.active = !user.active;
                }
                $scope.updateActiveUsers();
              }
              $scope.updateActiveUsers = function()
              {
                var params = $state.params;
                params.users = null;
                var activeItems = [];
                var numUsers = 0;
                angular.forEach($scope.users, function(item) {
                  if(item.active)
                  {
                    activeItems.push(item.id);
                  }
                  numUsers+=1;
                });
                if(activeItems.length > 0 && activeItems.length < numUsers)
                {
                  params.users = activeItems.join();
                } 
                $state.go($state.current.name,params);
              };
              $scope.scheduleEvent = function()
              {
                $rootScope.loading = true;
                $timeout(function(){
                  $state.go('schedule', { previous : { name: $state.current.name, params: $state.params } });
                },100);
              };
            }
          }
        }
      })

      .state({
        name:'month',
        parent:'calendar',
        params : {
          week: null,
          day:null,
        },
        url: ''
      })

      .state({

        name:'week',
        parent:'calendar',
        url:'/week',
        params : {
          week: null,
          day:null,
        },
        views:{

          'main' : 
          {
                  templateUrl: 'common/templates/calendar-week.tpl.html',
                  controller: function($scope,$stateParams,CalendarService,UserService)
                  { 


                    var week = 1;

                    if($stateParams.week === null)
                    {

                if($scope.isThisMonth)
                  week = CalendarService.getWeekOfMonth(moment());
                    
                    } else {
                      
                      week = $stateParams.week;

                    }

                    $scope.week = CalendarService.getWeek(week);
                  }

              },
              'navigation' : 
          {

            templateUrl: 'common/templates/navigation.tpl.html',
                  controller: function($state,$scope,$rootScope,$stateParams,CalendarService) 
                  {


                    $scope.activeView = 'week';

                    var week = 1;

                    if($stateParams.week === null )
                    {
                      if($scope.isThisMonth)
                        week = CalendarService.getWeekOfMonth(moment());
                    
                    } else {
                      
                      week = $stateParams.week;

                    }

                    var weekObj = CalendarService.getWeek(week);

                    var firstDay = weekObj.days[0].date;
                    var lastDay = weekObj.days[6].date;

                    $scope.month = $scope.selectedMonth.clone();
                    $scope.monthfmt = firstDay.format("MMM Do") + ' - ' + lastDay.format("MMM Do");

                    $scope.setMonthYear = function(newValue, oldValue)
                    {
                
                $rootScope.loading = true;
                
                var params = {};
                params.month = parseInt( newValue.format('M') );
                params.year = parseInt( newValue.format('YYYY') );
                params.week = null;
                params.day = null;
                params.users = $stateParams.users;
                params.reasons = $stateParams.reasons;
                params.departments = $stateParams.departments;

                      $state.go('month',params);

                    };

                    var numWeeks = CalendarService.getNumWeeks();

                    $scope.today = function()
                    {

                      if(!$scope.isThisMonth)
                        $rootScope.loading = true;

                      $state.go('week',{month:null,year:null,week:null,day:null});
                    };

                    $scope.next = function()
                    {
                      var nextParams = {};

                      //var week = 1;

                      if($stateParams.week !== null)
                      {
                        week = $stateParams.week;
                      }

                      if(week+1 <= numWeeks)
                      {
                        var nextWeek = week + 1;
                        nextParams.week = nextWeek;
                        
                        var weekObj = CalendarService.getWeek(nextWeek);
                        nextParams.day = weekObj.days[0].number;

                      } 
                      else 
                      {
                        nextParams.week = 1;
                        nextParams.day = 1;
                        var nextMoment = $scope.selectedMonth.clone();
                        nextMoment.add(1, 'month');
                        nextParams.month = nextMoment.format('M');
                        nextParams.year = nextMoment.format('YYYY');

                        $rootScope.loading = true;

                      }

                      $state.go('week',nextParams);
                    
                    };

                    $scope.prev = function()
                    {
                    
                      var prevParams = {};

                      if($stateParams.week !== null)
                      {
                        week = $stateParams.week;
                      }

                      if(week-1 > 0)
                      {
                        var prevWeek = week - 1;
                        prevParams.week = prevWeek;

                        var weekObj = CalendarService.getWeek(prevWeek);
                        prevParams.day = weekObj.days[0].number;

                      } 
                      else 
                      {
                        var prevMoment = $scope.selectedMonth.clone();
                        prevMoment.subtract(1, 'month');

                        var firstDay = moment(prevMoment).startOf('month').startOf('week');
                        var lastDay = moment(prevMoment).endOf('month').endOf('week');

                        var weeks = lastDay.diff(firstDay, 'weeks', true); 

                  prevParams.month = prevMoment.format('M');
                        prevParams.year = prevMoment.format('YYYY');
                        prevParams.week = Math.ceil(weeks);

                        $rootScope.loading = true;

                      }

                      $state.go('week',prevParams);
                    
                    };

                  }

              }

        }
      
      })

      .state({

        name:'day',
        parent:'calendar',
        url:'/day',
        params : {
          day: null
        },
        views:{

          'main' : 
          {
              
                  templateUrl: 'common/templates/calendar-day.tpl.html',
                  controller: function($scope,$stateParams,CalendarService,EventsService)
                  {

                    var day = 1;

                    if($stateParams.day === null )
                    {
                      if($scope.isThisMonth)
                        day = CalendarService.getDayOfMonth( $scope.thisMonth );
                    
                    } else {
                      
                      day = $stateParams.day;

                    }

                    var thisDay = CalendarService.getDay(day);

                    $scope.today = thisDay.date.format('dddd, MMMM Do');

                    $scope.holiday = EventsService.getHolidayByDate( thisDay.event );
                    $scope.events = EventsService.getEventsByDate( thisDay.event );

                  }

              },
              'navigation' : 
          {
            templateUrl: 'common/templates/navigation.tpl.html',
                  controller: function($state,$scope,$rootScope,$stateParams,CalendarService) 
                  {

              
                    $scope.activeView = 'day';

                    var day = 1;

                    if($stateParams.day === null )
                    {
                      if($scope.isThisMonth)
                  day = CalendarService.getDayOfMonth( $scope.thisMonth );
                    
                    } else {
                      
                      day = $stateParams.day;

                    }

                    var thisDay = CalendarService.getDay(day);

                    var numDays = CalendarService.getDaysInMonth($scope.selectedMonth);

                    $scope.month = thisDay.date.format('dddd, MMMM Do');

                    $scope.today = function()
                    {
                //var day = $scope.thisMonth.format('D');
                
                if(!$scope.isThisMonth)
                  $rootScope.loading = true;

                      $state.go('day',{month:null,year:null,day:null});
                    };

                    $scope.next = function()
                    {
                      var nextParams = {};

                      if(day+1 <= numDays)
                      {
                        day += 1;
                        nextParams.day = day;
                      } 
                      else 
                      {
                        nextParams.day = 1;
                        var nextMoment = $scope.selectedMonth.add(1, 'month');
                        nextParams.month = nextMoment.format('M');
                        nextParams.year = nextMoment.format('YYYY');

                        $rootScope.loading = true;

                      }

                      $state.go('day',nextParams);
                    
                    };

                    $scope.prev = function()
                    {
                    
                      var prevParams = {};

                      if(day-1 > 0)
                      {
                        day -= 1;
                        prevParams.day = day;
                      } 
                      else 
                      {
                        var prevMoment = $scope.selectedMonth.subtract(1, 'month');

                        var prevMonthDays = CalendarService.getDaysInMonth(prevMoment);

                  prevParams.month = prevMoment.format('M');
                        prevParams.year = prevMoment.format('YYYY');
                        prevParams.day = prevMonthDays;


                        $rootScope.loading = true;

                      }


                      $state.go('day',prevParams);
                    
                    };

                  }

              }

        }
      
      })

      .state({
        name:'schedule',
        parent:'root',
        url:'schedule/:id',
        params:{
          id:null,
          previous: null
        },
        views:{

          '' : 
          {
                  templateUrl: 'common/templates/schedule.tpl.html',
                  controller: function($rootScope){
                     $rootScope.loading = false;
                  }
              },
              'form@schedule' : 
          {

            templateUrl: 'common/templates/form.tpl.html',
                  controller:function($scope,$state,$timeout,$window,$rootScope,EventsService,DataService,UserService)
                  {
                    

                    var state = 'month';
                    var params = {};

                    if($state.params.previous !== null)
                    {
                      state = $state.params.previous.name;
                      params = $state.params.previous.params;
                    }


                    $scope.departments = UserService.getDepartments();
                    $scope.reasons = UserService.getReasons();
                    
                    var deptKeys = Object.keys($scope.departments);
                    $scope.numDepts = deptKeys.length;

                    $scope.users = UserService.getAllUsers();

                    $scope.event = {};
                    $scope.event.end = moment();
                    $scope.event.start = moment();
                    $scope.event.notify = false;
                    $scope.event.allday = true;
                    $scope.event.showavailable = false;

                    if($state.params.id)
                    {

                      var thisEvent = EventsService.getEventByID( $state.params.id );

                      if(!thisEvent)
                        $state.go(state,params);
                    
                      $scope.event = thisEvent;
                      
                    }
                
                    $scope.submitDisabled = false;

                    var i,hours = [];

                    for(i=0;i<=23;i++)
                    {
                      var hour= {};
                      hour.key = moment().hour(i).format('H');
                      hour.value = moment().hour(i).format('hh:[00] a');
                      hours.push(hour);
                    }

                    $scope.hours = hours;

                    $scope.showTime = false;

                    if($scope.event.allday === false)
                      $scope.showTime = true;

                    $scope.save = function(data)
                    {

                      $scope.submitDisabled = true;

                      var event = {};
                      
                      event.domain = $rootScope.activeDomain;

                      var start = data.start.clone();
                      var end = data.end.clone();

                      if($state.params.id)
                        event.id = $state.params.id;

                      event.employee = {};

                      event.employee.name = data.employee.name;
                      event.employee.id = data.employee.id;
                      event.employee.email = data.employee.email;
                      event.employee.department = data.employee.department;
                      event.employee.domain = data.employee.domain;
                      event.employee.cal_id = false;
                      
                      if(data.emp_cal_event_id !== undefined)
                        event.employee.cal_id = data.emp_cal_event_id;
                      
                      event.notify = data.notify;
                      event.showavailable = data.showavailable;

                      //other notifications
                      event.otherNotifications = '';

                      if(data.otherNotifications !== undefined)
                        event.otherNotifications = data.otherNotifications;

                      /*
                      if(data.notify !== undefined && data.notify.length > 0)
                        event.notify = data.notify;
                      */

                      event.department = data.employee.department;
                      event.type = data.type;
                      
                      if(data.allday === undefined || data.allday === false)
                      {
                        event.allday = false;
                        event.starttime = moment( start.format('YYYY-MM-DD') ).hour(data.starttime).format();
                        event.endtime = moment( end.format('YYYY-MM-DD') ).hour(data.endtime).format();

                      } else {

                        //advance one day for all day events
                        end.add(1,'day');

                        event.start = start.format('YYYY-MM-DD');
                        event.end = end.format('YYYY-MM-DD');
                        event.allday = true;
                      }
                      
                      $rootScope.loading = true;
                      
                      DataService.saveEvent(event).then(function(data)
                      {
                        $state.go(state,params);
                      });
                
                    };

                    $scope.toggleTime = function(value)
                    {
                      $scope.event.allday = !value;
                      $scope.event.endtime = '';
                      $scope.event.starttime = '';
                    };

                    $scope.discard = function(event)
                    { 
                      $rootScope.loading = true;

                      
                      $state.go(state,params);
                    };

                    $scope.delete = function()
                    { 

                      if(!$scope.event.id)
                        return;

                      var event = {};

                      event.domain = $rootScope.activeDomain;

                      event.id = $scope.event.id;
                      event.employee = $scope.event.employee;
                      
                      event.employee.cal_id = false;
                      
                      if($scope.event.emp_cal_event_id !== undefined)
                        event.employee.cal_id = $scope.event.emp_cal_event_id;

                      event.notify = $scope.event.notify;
                      event.showavailable = $scope.event.showavailable;

                      //other notifications
                      event.otherNotifications = '';

                      if($scope.event.otherNotifications !== undefined)
                        event.otherNotifications = $scope.event.otherNotifications;

                      /*
                      if($scope.event.notify !== undefined && $scope.event.notify.length > 0)
                        event.notify = $scope.event.notify;
                */

                      event.department = $scope.event.employee.department;
                      event.type = $scope.event.type;
                      
                      var start = $scope.event.start.clone();
                      var end = $scope.event.end.clone();

                      if($scope.event.allday === undefined || $scope.event.allday === false)
                      {
                        event.allday = false;
                        event.starttime = moment( start.format('YYYY-MM-DD') ).hour($scope.event.starttime).format();
                        event.endtime = moment( end.format('YYYY-MM-DD') ).hour($scope.event.endtime).format();

                      } else {

                        //advance one day for all day events
                        end.add(1,'day');

                        event.start = start.format('YYYY-MM-DD');
                        event.end = end.format('YYYY-MM-DD');
                        event.allday = true;
                      }

                      if ($window.confirm("Please confirm you want to delete this event?")) {
                            
                            $rootScope.loading = true;
                           
                            DataService.deleteEvent(event).then(function(data)
                        {
                          $state.go(state,params);
                        });
                  
                        } 

                        return;

                    };

                    $scope.isSelectable = function(date,type)
                    {
                      return date.isAfter();
                    };

                    $scope.setStartDate = function(newValue, oldValue)
                    { 

                      if(newValue.isBefore($scope.event.start))
                      {
                        $scope.$evalAsync(function () 
                        {
                          $scope.event.start = newValue;
                        });
                      }
                      
                    };

                    $scope.setEndDate = function(newValue, oldValue)
                    { 
                      $scope.$evalAsync(function () 
                      {
                        $scope.event.end = newValue;
                      });
                    };

                    $scope.setEndTime = function(value)
                    {

                      if(value === undefined)
                        return;

                      var hour = moment().hour(value);
                      $scope.event.endtime = hour.add(1,'hour').format('H');
                    };

            }
          }

        }
      });


  });

})(jQuery);
