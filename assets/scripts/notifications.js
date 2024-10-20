document.addEventListener('DOMContentLoaded', function() {
    function handleMessages(className, toastrFunction) {
        var elements = document.querySelectorAll(className);
        elements.forEach(function(element) {
            toastrFunction(element.innerHTML);
            console.log(element.innerHTML);
            element.remove();
        });
    }

    handleMessages('.error', toastr.error);
    handleMessages('.success', toastr.success);
    handleMessages('.info', toastr.info);
    handleMessages('.warning', toastr.warning);
});