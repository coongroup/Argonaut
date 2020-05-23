

angular.module('jq-multi-select', []).
directive('multiSelect', ['$rootScope', function ($rootScope) {
    function refresh(element) {
        $(element).multiSelect('refresh');
        $(".ms-container").append('<i class="glyph-icon icon-exchange"></i>');
    }

    function init(element, options) {
        var opt = options || {selectableHeader: "<div class='custom-header excludeHeader' style='text-align:center'>Excluded Conditions</div>",  
        selectionHeader: "<div class='custom-header includeHeader'  style='text-align:center'>Included Conditions</div>",
     afterSelect: function(){
   $rootScope.$broadcast('afterSelectEvent');
  },
  afterDeselect: function(){
    $rootScope.$broadcast('afterDeselectEvent');
  }};
        $(element).multiSelect(opt);
        $(".ms-container").append('<i class="glyph-icon icon-exchange"></i>');
    }

    return {
        restrict: 'EA',
        require: 'ngModel',
        scope: {
            multiSelect: '=',
            model1: '=ngModel'
        },
        link: function (scope, element, attrs, ngModel) {
            init(element, scope.msOptions);
            scope.$watch(function () {
                return (ngModel && ngModel.$modelValue) || scope.multiSelect;
            }, function (n) {
                refresh(element);
                $(element).multiSelect('select', ngModel.$modelValue);
            }, true);

        }
    };
}]);