var topImg  = 0;
var imgList = [
    {i: 0, "src": "2fkx"},
    {i: 1, "src": "3umx"},
    {i: 2, "src": "7baj"},
    {i: 3, "src": "8ejx"},
    {i: 4, "src": "8jnm"},
    {i: 5, "src": "8r5u"},
    {i: 6, "src": "bc4v"},
    {i: 7, "src": "9vt3"},
    {i: 8, "src": "h4ez"},
    {i: 9, "src": "i2nk"},
    {i: 10, "src": "i6ck"},
    {i: 11, "src": "i6yt"},
    {i: 12, "src": "iw4t"},
    {i: 13, "src": "k4ec"},
    {i: 14, "src": "rc3x"},
    {i: 15, "src": "sc2x"},
    {i: 16, "src": "si7x"},
    {i: 17, "src": "w4ec"},
    {i: 18, "src": "xc4r"},
];
function CodeImg(imgEl, clickEl, publicpath) {
    function checkImg() {
        var x = Math.floor(Math.random() * 19);
        if (topImg !== x) {
            topImg = x;
        } else {
            checkImg();
        }
        return x;
    }

    imgEl.attr("src", publicpath + "codeImg/" + (imgList[checkImg()].src) + ".png");
    clickEl.live("click", function () {
        imgEl.attr("src", publicpath + "codeImg/" + (imgList[checkImg()].src) + ".png");
    });
}