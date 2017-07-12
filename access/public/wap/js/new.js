$(".text-center").css("transform", "translateX(-" + (parseInt($("#header .text-center").css("width"))) / 2 + "px)");
$(".list>div>div>i").each(function () {
    this.canClick = true;
    $(this).click(function (event) {
        event.preventDefault();
        if (this.canClick) {
            $(this).children("b").html(parseInt($(this).children("b").html()) + 1);
            this.canClick = false;
        }
    });
});