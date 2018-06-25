(function($) {

    'use strict';

    function EventDirective(EventsService)
    {
        return {
            
            restrict: "EA",
            templateUrl: "common/templates/event.tpl.html",
            scope: {
                item: "=item",
            },
            link: function(scope) 
            {
                scope.showPopover=false; 
            },
            controller: function($scope,$rootScope,$timeout,$state,$window,$document,UserService)
            {
                var today = moment().startOf('hour');

                $scope.numdepts = UserService.numDepartments();

                $scope.top = '0px';
                //$scope.left = '0';

                $scope.togglePopOver = function(e)
                {
                    $scope.showPopover=!$scope.showPopover;

                    var target = $(e.currentTarget);
                    var day = $(e.currentTarget).closest('.day');
                    
                    var offset = (target.offset().top - day.offset().top) + target.height() + 5;
                    
                    $scope.top = offset + 'px';
                    
                    /*
                    if(day.top < 160)
                    {
                        var newTop = day.top - 20;
                        $scope.bottom = '0px';
                        $scope.left = '-190px';
                    }
                    */

                    $document.on('click', function(event)
                    {
                        if(event.target != e.target && !$(event.target).closest('.event-popover').length )
                        {
                            $scope.showPopover=false;
                            $scope.$apply();
                        }
                    });

                };

                $scope.isEditable = function(item)
                {   
                    var start = item.start.dateTime != null ? moment(item.start.dateTime) : moment(item.start.date).endOf('date');
                    return start.isSameOrAfter(today); 
                }

                $scope.edit = function(id)
                {   
                    $document.unbind('click');
                    $rootScope.loading = true;

                    $timeout(function(){
                        
                        $state.go('schedule', { 'id':id, previous : { name: $state.current.name, params: $state.params } });

                    },100);
                  
                
                };

            }

        };
        
    }

    angular.module('directives.event',[])
        .directive('event', EventDirective);
  
})(jQuery);
