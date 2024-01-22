<? if ($_COOKIE["DEVICE"] == "android" && $url[0] != "login" && $url[0] != "login-teacher" && $url[0] != "login-student") { ?>
<style>
    #footer-menu {
        display: flex;
        justify-content: center;
        overflow-y: hidden;
        overflow-x: scroll;
        width: 100%;
        height: 64px;
        position: fixed;
        background-color: #fff;
        bottom: 0;
        left: 0;
        z-index: 2;
        -webkit-box-shadow: 0 0 8px rgba(15, 15, 15, 0.15);
        box-shadow: 0 0 8px rgba(15, 15, 15, 0.15);
        align-items: center;
    }

    #footer-menu li {
        position: relative;
        line-height: 0.9;
    }

    #footer-menu a {
        padding: 16px 20px;
        display: inline-block;
        text-align: center;
        border-radius: 100px;
    }

    #footer-menu a:hover {
        color: #000;
    }
    #footer-menu a.hover {
        background-color: #E0E0E0;
        color: #ef741f;
    }

    #footer-menu li.active a {
        color: #ef741f;
    }

    #footer-menu li.active a::before {
        content: '';
        width: 100%;
        height: 9px;
        background-color: #ef741f;

        position: absolute;
        bottom: -4px;
        left: 0;

        border-top-left-radius: 10px;
        border-top-right-radius: 10px;
    }

    #footer-menu i {
        margin-bottom: 8px;
        display: block;
    }

    /* #footer-menu {
        overflow-y: hidden;
        overflow-x: hidden;
    } */
    /* Footer bottom menu */
    #footer-bottom-menu {
        width: 100%;
        background-color: #fff;
        position: fixed;
        transition: all 300ms ease;
        bottom: -1000px;
        left: 0;
        -webkit-box-shadow: 0 0 8px rgba(15, 15, 15, 0.15);
        box-shadow: 0 0 8px rgba(15, 15, 15, 0.15);
        border-top-left-radius: 30px;
        border-top-right-radius: 30px;
    }

    #footer-bottom-menu.show {
        bottom: 64px;
    }

    #footer-bottom-menu li {
        padding: 14px 26px;
        font-size: 18px;
        /* outline: 1px solid #ccc; */
    }

    #footer-bottom-menu a:hover,
    #footer-bottom-menu li.active a {
        color: #ef741f;
    }
</style>

<ul id="footer-menu"></ul>
<ul id="footer-bottom-menu"></ul>
<? } ?>

<!--**********************************
    Scripts
***********************************-->
<!-- Required vendors -->
<script src="theme/vora/vendor/global/global.min.js"></script>

<script src="theme/vora/vendor/bootstrap-select/dist/js/bootstrap-select.min.js"></script>

<!--  font awasome -->
<script src="https://kit.fontawesome.com/2ba10e709c.js" crossorigin="anonymous"></script>

<script src="theme/vora/vendor/chart.js/Chart.bundle.min.js"></script>
<!-- Owl carousel -->
<script src="theme/vora/vendor/owl-carousel/owl.carousel.js"></script>
<!-- Chart piety plugin files -->
<script src="theme/vora/vendor/peity/jquery.peity.min.js"></script>
<script src="theme/vora/vendor/jquery-nice-select/js/jquery.nice-select.min.js"></script>
<? if ($url[0] == "home") { ?>
    <!-- Apex Chart -->
    <script src="theme/vora/vendor/apexchart/apexchart.js"></script>
    <!-- Dashboard 1 -->
    <script src="theme/vora/js/dashboard/dashboard-1.js"></script>
<? } ?>

<!-- Select2 -->
<script src="theme/vora/vendor/select2/js/select2.full.min.js"></script>
<script src="theme/vora/js/plugins-init/select2-init.js"></script>
<!--  -->
<script src="theme/vora/js/custom.min.js"></script>
<script src="theme/vora/js/dlabnav-init.js"></script>


<? if ($url[0] == "calendar") { ?>
    <!-- calendar -->
    <script src="theme/vora/vendor/jqueryui/js/jquery-ui.min.js"></script>
    <script src="theme/vora/vendor/moment/moment.min.js"></script>
    <script src="theme/vora/vendor/fullcalendar-5.11.0/lib/main.min.js"></script>
    <script src="theme/vora/js/plugins-init/fullcalendar-init.js"></script>
    <!-- <script src="theme/vora/js/plugins-init/fullcalendar-init.js"></script> -->
<? } ?>

<!-- Excel -->
<script src="modules/excel/excel.js"></script>

<? if ($_COOKIE["DEVICE"] == "android" && $url[0] != "login" && $url[0] != "login-teacher" && $url[0] != "login-student") { ?>
<script>
    var limit = 0;
    $("#menu").find("a.has-arrow").each(function(){
        var nav_text = $(this).find(".nav-text").text();
        var nav_icon = $(this).find("i").attr("class");
        $(this).attr("id", "menu-item-" + limit);
        // console.log(nav_text);
        // console.log(nav_icon);
        // console.log($("#footer-menu"));

        var active = false;
        if ($(this).parent("li").hasClass("mm-active")) active = true;

        $("#footer-menu").append('<li'+(active ? ' class="active"' : '')+' data-menu-item="menu-item-'+limit+'">'
            +'<a href="javascript:void(0)" style="font-size: 9px;">'
                +'<i class="'+nav_icon+'" style="font-size:18px;"></i> ' + nav_text
            +'</a>'
        +'</li>');

        limit++;
    });

    $("#footer-menu > li").on("click", function(){
        $("#footer-menu").find("a").removeClass("hover");
        $("#footer-bottom-menu").html("");

        var menu_id = $(this).attr("data-menu-item");
        var active_menu = $("#footer-bottom-menu").attr("data-active-menu");
        $("#footer-bottom-menu").attr("data-active-menu", menu_id);

        if (active_menu == menu_id) {
            $("#footer-bottom-menu").removeClass("show").removeAttr("data-active-menu");
            $(this).find("a").removeClass("hover");
        } else {
            $("#footer-bottom-menu").addClass("show");
            $(this).find("a").addClass("hover");
        }

        var links = $("#" + menu_id).parents("li").find("ul").find("a");

        $(links).each(function(){
            var text = $(this).text();
            var href = $(this).attr("href");
            var active = $(this).hasClass("mm-active");

            $("#footer-bottom-menu").prepend(
                '<li class="'+(active ? "active" : "")+'"><a href="'+href+'">'+text+'</a></li>'
            );
        })
    });
</script>
<? } ?>

<script>
    $("#menu > li").hover(function(){
        $(document).find(".ps__rail-y").hide();
    }).mouseleave(function(){
        $(document).find(".ps__rail-y").show();
    })
    //  function addHideee() {
    //     $(".disp").hide();
    // }
    // function removeHideee() {
    //     $(".disp").show();
    // }
    
    $("#filter").find("select").not("*[data-skip-this-input]").on("change", function(){
        updateTable($(this).attr("name"));
    });

    $("#filter").find("input").not("*[data-skip-this-input]").on("input", function(){
        updateTable($(this).attr("name"));
    });

    $("#submit-date").on("click", function(){
        updateTable($(this).attr("name"));
    });

    function findGetParameter(parameterName) {
        var result = "",
            tmp = [];
        location.search
            .substr(1)
            .split("&")
            .forEach(function (item) {
            tmp = item.split("=");
            if (tmp[0] === parameterName) result = decodeURIComponent(tmp[1]);
            });
        return result;
    }

    function updateTable(input_name = false) {
        console.log("updateTable: " + input_name);

        var q = $( "#filter" ).serialize();
        var url = '/<?=$url[0]?>?' + q;
        $.ajax({
            url: url,
            type: "GET",
            dataType: "html",
            beforeSend: function () {
                $("#preloader").css("background", "transparent");
                $(".sk-three-bounce").css("background", "transparent");
                $("#preloader").css("backdrop-filter", "blur(10px)");
                $(".sk-three-bounce").css("backdrop-filter", "blur(10px)");
                $("#preloader").css("display", "block");
                $("#preloader").css("z-index", "9");
                // backdrop-filter: blur(10px);
		        // $('#main-wrapper').addClass('show');
                console.log("beforeSend is work!");
            },
            success: function(data) {

                window.history.pushState($(data).find("title").text(), "Title", url);

                // console.log($(data).find("#group_id").html());
                $("#group_id").html($(data).find("#group_id").html());
                $("#group_id").selectpicker("refresh");
                
                $("#update_students").html($(data).find("#update_students").html());
                $('#update_students').selectpicker('refresh');
                $("#table").html($(data).find("#table").html());
                
                $("#subModal2Table").html($(data).find("#subModal2Table").html()); // modal di update qilish uchun
                
                var tableWidth = $("#table").width();
                $(".setWidth").width(tableWidth);

                if($(data).find("#pagination-wrapper").html() == undefined) {
                    $("#pagination-wrapper").html('');
                } else {
                    $("#pagination-wrapper").html($(data).find("#pagination-wrapper").html());
                    var paginations = $(data).find("#pagination-wrapper").find("ul").find("li").length;
                    if (paginations > 3) {
                        $("#pagination-wrapper").show();
                    } else {
                        $("#pagination-wrapper").hide();
                    }
                }
                $(".breadcrumb").html($(data).find(".breadcrumb").html());
                $(".breadcrumb2").html($(data).find(".breadcrumb2").html());

                // var newA = q.split("code=");
                // var code = newA.slice(-1).join('');
                var code = findGetParameter("code");
                console.log(code);
                
                
                if (code != $("#update_students").val()) {
                    $("#update_students").change();
                }
                
                // Loader on filter change
                $("#preloader").css("background", "white");
                $(".sk-three-bounce").css("background", "white");
                $("#preloader").css("backdrop-filter", "blur(0)");
                $(".sk-three-bounce").css("backdrop-filter", "blur(0)");
                $("#preloader").css("display", "none");
                $("#preloader").css("z-index", "1");
                
            }
        })
    }

    $("#input-search").on("input", function(){
        updateTable();
    });

    $(document).on("click", ".page-link", function(e){
        if ($(this).hasClass("active")) return;
        e.preventDefault();

        var url = $(this).attr("href");

        $.get(url, function(data){
            window.history.pushState($(data).find("title").text(), "Title", url);
            $("#table").html($(data).find("#table").html());
            if($(data).find("#pagination-wrapper").html() == undefined) {
                    $("#pagination-wrapper").html('');
                } else {
                    $("#pagination-wrapper").html($(data).find("#pagination-wrapper").html());
                }
            // $("#pagination-wrapper").html($(data).find("#pagination-wrapper").html());
        });
    });

    $("#top-search").on("input", function(){
        $("*[name='q']").val(
            $(this).val()
        );
        updateTable();
    })
</script>