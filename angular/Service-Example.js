(function() {

  'use strict';

  function _buildMonth(start, current) {
      
      var month = [];
      var done = false; 
      var date = start.clone();
      var monthIndex = date.month();
      var count = 0;

      while (!done) 
      {
          month.push({ days: _buildWeek(date.clone(), current) });
          date.add(1, "w");
          done = count++ > 2 && monthIndex !== date.month();
          monthIndex = date.month();
      }

      return month;

  }

  function _buildWeek(date, month) {
      
      var days = [];
      
      for (var i = 0; i < 7; i++) {
          
          days.push({
              name: date.format("dd").substring(0, 1),
              number: date.date(),
              isCurrentMonth: date.month() === month.month(),
              isToday: date.isSame(new Date(), "day"),
              date: date,
              event: date.format("YYYY-MM-DD")
          });
          
          date = date.clone();
          
          date.add(1, "d");

      }

      return days;
  }

  function CalendarService()
  {
      
      var service, view,calendar;

      service = {};

      service.setMonthlyCalendar = function(month) 
      {

        //clone the mutable object
        var thisMonth = month.clone();

        //set the month to the first day
        thisMonth.startOf('month');

        //clone thisMonth
        var startMonth = thisMonth.clone();

        //set it to the beginning of the week on sunday
        startMonth.startOf('week');

        calendar = _buildMonth(startMonth, thisMonth);
      
      };

      service.getMonthlyCalendar = function() 
      {
        return calendar;
      };

      service.getNumWeeks = function()
      {
        return calendar.length;
      };

      service.getWeek = function(week)
      {
        return calendar[week-1];
      };
      
      service.getWeekOfMonth = function(month)
      {
        return month.week() - moment(month).startOf('month').week() + 1;
      };

      service.getDayOfMonth = function(month)
      {
        return month.date();
      };

      service.getDay = function(day)
      {

        var i,j,weeks = this.getMonthlyCalendar();

        for(i=0; i<weeks.length; i++)
        {
          for(j=0; j<weeks[i].days.length; j++)
          {
            if(weeks[i].days[j].isCurrentMonth === true && weeks[i].days[j].number === day)
            {
              return weeks[i].days[j];
            }
          }
          
        }

      };

      service.getDaysInMonth = function(month)
      { 
        return month.daysInMonth();
      };

      return service;
  
  }

  angular.module('common.services.calendar', [])
    .factory('CalendarService', CalendarService);
  
})();
