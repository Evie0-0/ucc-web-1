<?php
declare(strict_types=1);

require_once __DIR__ . '/../../bootstrap.php';
require_once __DIR__ . '/../../app/Support/helper.php';

use Core\User;

$pageTitle = 'Dashboard';

User::requireAuthentication();
?>

<!DOCTYPE html>
<html lang="en">
<head>
	<meta charset="UTF-8">
	<meta name="viewport" content="width=device-width,initial-scale=1.0">
    <title>UCC Admin | <?= e($pageTitle ?? '') ?> </title>
	<link rel="icon" type="image/png" href="/admin/assets/images/ucc-LOGO.png">
	<link rel="preconnect" href="https://fonts.googleapis.com">
	<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
	<link href="https://fonts.googleapis.com/css2?family=DM+Sans:wght@400;500;700&family=Libre+Baskerville:wght@700&display=swap" rel="stylesheet">
	<script src="https://unpkg.com/lucide@latest"></script>
	<link rel="stylesheet" href="/admin/assets/css/style.css">
</head>
<body>
	<div class="dashboard-layout">
        <?php require_once __DIR__ . '/template/sidebar.php' ?>
		<main class="main-content">
            <?php require_once __DIR__ . '/template/header.php' ?>
			<section class="content-area">
				<div class="heading-row">
					<div>
                        <h2>Good day, <?= $_SESSION['username'] ?></h2>
						<p>Here's an overview of your university website.</p>
					</div>

					<div class="tagline">
						<span>A BRIGHTER TOMORROW TOGETHER</span>
						<div class="tag-line"></div>
					</div>
				</div>

				<div class="stats-grid">

					<div class="stat-card">
						<div class="icon green">
							<i data-lucide="clipboard-list"></i>
						</div>

						<div class="stat-content">
							<h4>Total Posts</h4>
							<h3>128</h3>
							<p><span class="up">▲ +12%</span> from last month</p>
						</div>

						<div class="chart-box">
							<div id="chartPosts" class="apex-mini"></div>
						</div>
					</div>

					<div class="stat-card">
						<div class="icon gold">
							<i data-lucide="clock-3"></i>
						</div>

						<div class="stat-content">
							<h4>Pending Posts</h4>
							<h3>7</h3>
							<p><span class="up">▲ +2%</span> from last month</p>
						</div>

						<div class="chart-box">
							<div id="chartPending" class="apex-mini"></div>
						</div>
					</div>

					<div class="stat-card">
						<div class="icon gray">
							<i data-lucide="archive"></i>
						</div>

						<div class="stat-content">
							<h4>Archived Posts</h4>
							<h3>36</h3>
							<p><span class="down">▼ -5%</span> from last month</p>
						</div>

						<div class="chart-box">
							<div id="chartArchived" class="apex-mini"></div>
						</div>
					</div>

					<div class="stat-card">
						<div class="icon red">
							<i data-lucide="users-round"></i>
						</div>

						<div class="stat-content">
							<h4>Users</h4>
							<h3>1,024</h3>
							<p><span class="up">▲ +8%</span> from last month</p>
						</div>

						<div class="chart-box">
							<div id="chartUsers" class="apex-mini"></div>
						</div>
					</div>

					<div class="stat-card">
						<div class="icon blue">
							<i data-lucide="chart-column-big"></i>
						</div>

						<div class="stat-content">
							<h4>Real-time Visitor</h4>
							<h3>243</h3>
							<p><span class="up">▲ +15%</span> from last hour</p>
						</div>

						<div class="chart-box">
							<div id="chartVisitors" class="apex-mini"></div>
						</div>
					</div>

				</div>

                <div class="table-card chart-card">
                    <div class="table-head">
                        <div class="table-title">
                            <div class="table-icon">
                                <i data-lucide="trophy"></i>
                            </div>
                            <h3>Top 5 Most Viewed Posts</h3>
                        </div>
                        <a href="#">
                            View All Posts
                            <i data-lucide="arrow-right"></i>
                        </a>
                    </div>

                    <div id="topPostsChart" class="top-post-chart"></div>
                </div>

			</section>
		</main>
	</div>

<script src="https://cdn.jsdelivr.net/npm/apexcharts"></script>

<script>
lucide.createIcons();

document.querySelectorAll(".menu-title").forEach(btn=>{
    btn.addEventListener("click",()=>{
        btn.parentElement.classList.toggle("open");
    });
});

function createSparkline(id,data,color){
    new ApexCharts(document.querySelector(id),{
        chart:{
            type:"area",
            height:56,
            sparkline:{enabled:true},
            toolbar:{show:false},
            animations:{
                enabled:true,
                easing:"easeinout",
                speed:900
            }
        },
        series:[{data}],
        stroke:{
            curve:"smooth",
            width:3
        },
        colors:[color],
        fill:{
            type:"gradient",
            gradient:{
                shadeIntensity:.35,
                opacityFrom:.45,
                opacityTo:0,
                stops:[0,100]
            }
        },
        markers:{size:0},
        grid:{show:false},
        tooltip:{enabled:false}
    }).render();
}

createSparkline("#chartPosts",[18,25,21,32,29,40,52],"#38d96c");
createSparkline("#chartPending",[22,27,25,31,28,36,40],"#d0a214");
createSparkline("#chartArchived",[46,42,38,36,33,31,28],"#9aa5aa");
createSparkline("#chartUsers",[14,18,17,24,28,31,36],"#2fcb64");
createSparkline("#chartVisitors",[12,26,18,34,28,40,48],"#38d96c");

new ApexCharts(document.querySelector("#topPostsChart"),{
chart:{
    type:"bar",
    height:390,
    toolbar:{show:false},
    fontFamily:"DM Sans, sans-serif",
    background:"transparent",
    animations:{
        enabled:true,
        easing:"easeinout",
        speed:900
    }
},
series:[{
    name:"Views",
    data:[5482,3921,2876,2153,1904]
}],
colors:["#0b744b","#1a8b5d","#37a56f","#58bf85","#d8b056"],
plotOptions:{
    bar:{
        horizontal:false,
        columnWidth:"50%",
        borderRadius:14,
        borderRadiusApplication:"end",
        distributed:true
    }
},
dataLabels:{
    enabled:true,
    offsetY:-10,
    style:{
        fontSize:"12px",
        fontWeight:700,
        colors:["#27483f"]
    },
    formatter:function(val){
        return val.toLocaleString();
    }
},
stroke:{
    show:true,
    width:2,
    colors:["rgba(255,255,255,.55)"]
},
fill:{
    type:"gradient",
    gradient:{
        shade:"light",
        type:"vertical",
        shadeIntensity:.35,
        gradientToColors:["#42d77e","#5bdd8f","#7be4a3","#97e8b7","#efcb73"],
        opacityFrom:.98,
        opacityTo:.72,
        stops:[0,100]
    }
},
xaxis:{
    categories:[
        "UCC Heroes",
        "SSC",
        "VISION",
        "Dekalidad",
        "Community"
    ],
    labels:{
        style:{
            fontSize:"12px",
            fontWeight:600,
            colors:"#42544c"
        },
        rotate:0
    },
    axisBorder:{show:false},
    axisTicks:{show:false}
},
yaxis:{
    max:6000,
    tickAmount:6,
    title:{
        text:"Views",
        offsetX:-10,
        style:{
            fontSize:"13px",
            fontWeight:700,
            color:"#365046"
        }
    },
    labels:{
        style:{
            fontSize:"12px",
            colors:"#5a6b65"
        },
        formatter:function(val){
            return val.toLocaleString();
        }
    }
},
grid:{
    borderColor:"#ebe5d8",
    strokeDashArray:5,
    padding:{
        left:10,
        right:10,
        top:12
    }
},
legend:{
    show:true,
    position:"top",
    horizontalAlign:"center",
    fontSize:"13px",
    fontWeight:600,
    markers:{
        width:18,
        height:10,
        radius:999
    }
},
tooltip:{
    theme:"light",
    style:{
        fontSize:"13px"
    },
    y:{
        formatter:function(val){
            return val.toLocaleString()+" views";
        }
    }
},
states:{
    hover:{
        filter:{
            type:"lighten",
            value:.18
        }
    }
}
}).render();
</script>
</body>
</html>
