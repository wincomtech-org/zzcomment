function preview(file,o)  
{  console.log(this.files)
    var prevDiv = document.getElementById('preview'+o);  
    if (file.files && file.files[0]) { 
        var reader = new FileReader();  
        reader.onload = function(evt) {  
            prevDiv.innerHTML = '<img src="' + evt.target.result + '" />';
        }    
        reader.readAsDataURL(file.files[0]);  
    } else {  
        console.log("ie")
        prevDiv.innerHTML = '<div class="img" style="filter:progid:DXImageTransform.Microsoft.AlphaImageLoader(sizingMethod=scale,src=\'' + file.value + '\'"></div>';  
    }  
}
$(function () {
    $("#price").val(powAmount(1000, 2));
    $(".acount li a").click(function () {
        $(".other").hide();
        $(".acount li a").removeClass("ss");
        $(this).addClass("ss");
            var money = $(this).attr("hide") < 1 ? 1 : $(this).attr("hide");
        $(".pri").html("￥" + powAmount(money, 2));
        $("#price").val(powAmount(money, 2));
    });
    $(".acount li a.ot").click(function () {
        $(".other").show();
    });
    $("#price").blur(function () {
        var money = $(this).val();
            if (money < 1) {
                money = 1;
                $(this).val(powAmount(money, 2));
            } else {
                $(this).val(powAmount(money, 2));
            }
        $(".pri").html("￥" + powAmount(money, 2));
    });
});        
function powAmount(amount, _pow_) {
    var amount_bak = amount;
    var base = 10;
    if (isNaN(amount)) {
        return "500.00";
    }
    amount = Math.round((amount - Math.floor(amount)) * Math.pow(base, _pow_));
    amount = amount < 10 ? '.0' + amount : '.' + amount
    amount = Math.floor(amount_bak) + amount;
    return amount;
        }
(function(){
    $("input[type=radio]").change(function(){
        var s=$("#weixin").prop("checked");
        if(s==true){
            $(".pay_f").hide();
            $("#example4").click();
        }else{
            $(".pay_f").show();
        }});}());


var wh=$(window).width();
var pl=$(".per_left");
if(wh<=1200){
$(".per_left").remove();
$(".mobile").append(pl);
}
$(".mobilemenu").click(function(){
$(".per_left").toggle(500);
});
function anycheck(form){
    var total=0;
    var max=form.week.length;
    for(var idx=0;idx<max;idx++){
        if(eval("document.playlist.week["+idx+"].checked")==true){
            total+=1;
        }
    }
    var p1=$("#price1").text();
    console.log(p1)
     $("#price2").text(p1*total)
}

function wei(){
    var dd=$("#weixin").is(":checked");
    if(dd==true){
        $(".ewm").show();
    }
}

$(".cl").click(function(){
    var r=confirm("确定取消支付吗？");
    console.log($(this).parent())
    if(r==true){
        $(this).parent().hide();
    }
});
// $(".ib").mouseenter(function(){
//     $(this).find("span").show();
// });
// $(".ib").mouseleave(function(){
//     $(this).find(".btn span").hide();
// });

// $(".dyml").click(function(){
// $(".dingo").show();
// });
// $(".stj").click(function(){
// $(".dingo").show();
// });


$("#all").click(function(){   
    if(this.checked){    
        $("#lk :checkbox").prop("checked", true);   
    }else{    
        $("#lk :checkbox").prop("checked", false); 
    }    
});

var s1=$(".form3_list2 li").length;
for (var i =0; i<=s1-1; i++) {
    var a1=document.getElementsByName("week")[i];
    console.log(i)
    var a2=document.getElementsByClassName("a22")[i].innerText;
        if(a2==0){
            a1.setAttribute("disabled", "disabled"); 
        }
}


