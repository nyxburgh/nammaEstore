<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8"><meta name="viewport" content="width=device-width,initial-scale=1.0">
<title><?= e($title??'Dashboard') ?> — Namma E Store Vendor</title>
<link href="https://fonts.googleapis.com/css2?family=Sora:wght@600;700;800&family=Plus+Jakarta+Sans:wght@300;400;500;600;700&display=swap" rel="stylesheet">
<style>
:root{
  --pink-deep:#c2185b;--pink-main:#e91e8c;--pink-mid:#f06292;
  --pink-soft:#fce4ec;--pink-pale:#fdf2f7;
  --blue-main:#1565c0;--blue-light:#1976d2;--blue-pale:#e3f2fd;
  --gold:#f9a825;--dark:#1a1a2e;--dark2:#2d2d44;
  --gray:#6b7280;--gray-light:#f5f5f5;
  --green:#2e7d32;--green-pale:#e8f5e9;
  --orange:#e65100;--orange-pale:#fff3e0;
  --red:#c62828;--red-pale:#ffebee;
  --sidebar-w:240px;--header-h:58px;
  --radius:14px;--transition:0.25s cubic-bezier(0.4,0,0.2,1);
  --shadow:0 4px 20px rgba(233,30,140,.10);
}
*,*::before,*::after{box-sizing:border-box;margin:0;padding:0;}
html{scroll-behavior:smooth;}
body{font-family:'Plus Jakarta Sans',sans-serif;background:#f5f0f8;color:var(--dark);min-height:100vh;}

/* ═══ TOP BAR ═══════════════════════════════════════════════ */
.top-bar{position:fixed;top:0;left:0;right:0;z-index:900;height:var(--header-h);background:linear-gradient(135deg,#c2185b 0%,#e91e8c 50%,#1565c0 100%);display:flex;align-items:center;justify-content:space-between;padding:0 20px;box-shadow:0 2px 20px rgba(194,24,91,.3);}
.top-bar-left{display:flex;align-items:center;gap:14px;}
.sidebar-toggle{background:rgba(255,255,255,.15);border:none;color:white;width:34px;height:34px;border-radius:9px;cursor:pointer;font-size:1rem;display:none;align-items:center;justify-content:center;transition:background var(--transition);}
.sidebar-toggle:hover{background:rgba(255,255,255,.25);}
@media(max-width:900px){.sidebar-toggle{display:flex;}}
.top-logo{font-family:'Sora',sans-serif;font-size:1.4rem;font-weight:700;color:white;text-decoration:none;letter-spacing:-.5px;}
.top-logo span{color:#42a5f5;}
.vendor-badge-top{background:rgba(255,255,255,.18);color:white;font-size:.68rem;font-weight:700;padding:3px 10px;border-radius:50px;letter-spacing:.5px;text-transform:uppercase;}
.top-bar-right{display:flex;align-items:center;gap:10px;}
.top-icon-btn{background:rgba(255,255,255,.15);border:none;color:white;width:34px;height:34px;border-radius:9px;cursor:pointer;font-size:1rem;display:flex;align-items:center;justify-content:center;position:relative;transition:background var(--transition);text-decoration:none;}
.top-icon-btn:hover{background:rgba(255,255,255,.28);}
.notif-dot{position:absolute;top:4px;right:4px;width:8px;height:8px;border-radius:50%;background:var(--gold);border:1.5px solid white;pointer-events:none;}
.vendor-avatar{width:34px;height:34px;border-radius:50%;background:rgba(255,255,255,.25);border:2px solid rgba(255,255,255,.5);display:flex;align-items:center;justify-content:center;font-size:1rem;cursor:pointer;color:white;font-weight:700;transition:all var(--transition);}
.vendor-avatar:hover{background:rgba(255,255,255,.35);}
.top-vendor-name{color:white;font-size:.82rem;font-weight:600;}
@media(max-width:600px){.top-vendor-name,.vendor-badge-top{display:none;}}

/* ═══ LAYOUT ════════════════════════════════════════════════ */
.dash-layout{display:flex;padding-top:var(--header-h);min-height:100vh;}

/* ═══ SIDEBAR ═══════════════════════════════════════════════ */
.sidebar{width:var(--sidebar-w);flex-shrink:0;background:var(--dark);min-height:calc(100vh - var(--header-h));position:fixed;top:var(--header-h);left:0;bottom:0;overflow-y:auto;z-index:800;transition:transform var(--transition);scrollbar-width:thin;scrollbar-color:rgba(255,255,255,.1) transparent;padding-bottom:80px;}
.sidebar::-webkit-scrollbar{width:4px;}
.sidebar::-webkit-scrollbar-thumb{background:rgba(255,255,255,.1);}
@media(max-width:900px){.sidebar{transform:translateX(calc(-1 * var(--sidebar-w)));}.sidebar.open{transform:translateX(0);box-shadow:6px 0 30px rgba(0,0,0,.3);}}
.vendor-card-side{padding:20px 16px;border-bottom:1px solid rgba(255,255,255,.07);text-align:center;}
.vendor-ava-big{width:60px;height:60px;border-radius:50%;margin:0 auto 10px;background:linear-gradient(135deg,var(--pink-main),var(--blue-main));border:2.5px solid var(--pink-main);display:flex;align-items:center;justify-content:center;font-size:1.8rem;font-weight:700;color:white;}
.vendor-card-side h3{color:white;font-size:.9rem;font-weight:700;margin-bottom:3px;}
.vendor-card-side p{color:rgba(255,255,255,.5);font-size:.7rem;margin-bottom:10px;}
.plan-badge{display:inline-flex;align-items:center;gap:5px;background:linear-gradient(90deg,var(--gold),#ffb300);color:var(--dark);font-size:.68rem;font-weight:800;padding:3px 12px;border-radius:50px;}
.nav-section{padding:14px 0 4px;}
.nav-section-label{color:rgba(255,255,255,.3);font-size:.62rem;font-weight:700;letter-spacing:1.5px;text-transform:uppercase;padding:0 18px 6px;}
.nav-item{display:flex;align-items:center;gap:11px;padding:10px 18px;color:rgba(255,255,255,.6);font-size:.82rem;font-weight:500;cursor:pointer;border:none;background:transparent;width:100%;text-align:left;font-family:'Plus Jakarta Sans',sans-serif;transition:all var(--transition);position:relative;text-decoration:none;}
.nav-item:hover{color:white;background:rgba(255,255,255,.06);}
.nav-item.active{color:white;background:linear-gradient(90deg,rgba(233,30,140,.25),rgba(233,30,140,.05));border-left:3px solid var(--pink-main);}
.nav-icon{font-size:1rem;width:20px;text-align:center;flex-shrink:0;}
.nav-label{flex:1;}
.nav-count{background:var(--pink-main);color:white;font-size:.6rem;font-weight:800;min-width:18px;height:18px;border-radius:9px;padding:0 5px;display:flex;align-items:center;justify-content:center;}
.nav-count.gold{background:var(--gold);color:var(--dark);}
.sidebar-divider{height:1px;background:rgba(255,255,255,.07);margin:6px 0;}
.sidebar-overlay{display:none;position:fixed;inset:0;background:rgba(0,0,0,.5);z-index:799;}
.sidebar-overlay.open{display:block;}

/* ═══ MAIN CONTENT ══════════════════════════════════════════ */
.main-content{flex:1;margin-left:var(--sidebar-w);padding:24px;min-width:0;transition:margin var(--transition);}
@media(max-width:900px){.main-content{margin-left:0;padding:16px;}}
@media(max-width:500px){.main-content{padding:12px;}}

/* ═══ FLASH ═════════════════════════════════════════════════ */
.flash-alert{display:flex;align-items:center;gap:8px;padding:11px 16px;border-radius:10px;font-size:.85rem;margin-bottom:16px;}
.flash-alert.success{background:var(--green-pale);color:var(--green);border:1px solid #a7f3d0;}
.flash-alert.error,.flash-alert.danger{background:var(--red-pale);color:var(--red);border:1px solid #fca5a5;}
.flash-alert.info{background:var(--blue-pale);color:var(--blue-main);border:1px solid #93c5fd;}

/* ═══ SECTION SYSTEM ════════════════════════════════════════ */
.dash-section{display:none;}
.dash-section.active{display:block;}

/* ═══ PAGE HEADER ═══════════════════════════════════════════ */
.page-hd{display:flex;align-items:flex-start;justify-content:space-between;margin-bottom:22px;gap:12px;flex-wrap:wrap;}
.page-hd h1{font-family:'Sora',sans-serif;font-size:1.5rem;color:var(--dark);display:flex;align-items:center;gap:10px;}
.page-hd p{font-size:.82rem;color:var(--gray);margin-top:2px;}
.hd-actions{display:flex;gap:8px;flex-wrap:wrap;}

/* ═══ BUTTONS ═══════════════════════════════════════════════ */
.btn-pink{display:inline-flex;align-items:center;gap:6px;background:linear-gradient(90deg,var(--pink-main),var(--pink-deep));color:white;border:none;padding:9px 18px;border-radius:10px;font-size:.82rem;font-weight:700;cursor:pointer;font-family:'Plus Jakarta Sans',sans-serif;transition:all var(--transition);white-space:nowrap;text-decoration:none;}
.btn-pink:hover{box-shadow:0 4px 16px rgba(233,30,140,.4);transform:translateY(-1px);}
.btn-outline{display:inline-flex;align-items:center;gap:6px;background:white;color:var(--pink-deep);border:1.5px solid var(--pink-mid);padding:8px 16px;border-radius:10px;font-size:.82rem;font-weight:700;cursor:pointer;font-family:'Plus Jakarta Sans',sans-serif;transition:all var(--transition);white-space:nowrap;text-decoration:none;}
.btn-outline:hover{background:var(--pink-pale);}
.btn-blue{display:inline-flex;align-items:center;gap:6px;background:var(--blue-main);color:white;border:none;padding:9px 18px;border-radius:10px;font-size:.82rem;font-weight:700;cursor:pointer;font-family:'Plus Jakarta Sans',sans-serif;transition:all var(--transition);text-decoration:none;}
.btn-blue:hover{background:var(--blue-light);}
.btn-sm{padding:5px 12px!important;font-size:.75rem!important;border-radius:7px!important;}
.btn-danger{background:white;color:var(--red);border:1.5px solid var(--red);display:inline-flex;align-items:center;gap:6px;padding:5px 12px;border-radius:7px;font-size:.75rem;font-weight:700;cursor:pointer;font-family:'Plus Jakarta Sans',sans-serif;transition:all var(--transition);}
.btn-danger:hover{background:var(--red-pale);}

/* ═══ STATS GRID ════════════════════════════════════════════ */
.stats-grid{display:grid;grid-template-columns:repeat(4,1fr);gap:14px;margin-bottom:22px;}
@media(max-width:1100px){.stats-grid{grid-template-columns:repeat(2,1fr);}}
@media(max-width:480px){.stats-grid{grid-template-columns:repeat(2,1fr);gap:10px;}}
.stat-card{background:white;border-radius:var(--radius);padding:18px;border:1.5px solid #f0e6ef;position:relative;overflow:hidden;transition:all var(--transition);}
.stat-card:hover{transform:translateY(-2px);box-shadow:var(--shadow);}
.stat-card::before{content:'';position:absolute;top:0;left:0;right:0;height:3px;border-radius:var(--radius) var(--radius) 0 0;}
.stat-card.pink::before{background:linear-gradient(90deg,var(--pink-main),var(--pink-deep));}
.stat-card.blue::before{background:linear-gradient(90deg,var(--blue-main),var(--blue-light));}
.stat-card.green::before{background:linear-gradient(90deg,#2e7d32,#43a047);}
.stat-card.gold::before{background:linear-gradient(90deg,var(--gold),#ffb300);}
.stat-icon{width:42px;height:42px;border-radius:11px;display:flex;align-items:center;justify-content:center;font-size:1.2rem;margin-bottom:12px;}
.stat-card.pink .stat-icon{background:var(--pink-soft);}
.stat-card.blue .stat-icon{background:var(--blue-pale);}
.stat-card.green .stat-icon{background:var(--green-pale);}
.stat-card.gold .stat-icon{background:#fff8e1;}
.stat-label{font-size:.72rem;color:var(--gray);font-weight:600;text-transform:uppercase;letter-spacing:.5px;margin-bottom:4px;}
.stat-value{font-family:'Sora',sans-serif;font-size:1.6rem;font-weight:700;color:var(--dark);margin-bottom:6px;}
.stat-change{font-size:.72rem;display:flex;align-items:center;gap:4px;}
.stat-change.up{color:var(--green);}
.stat-change.down{color:var(--red);}
.stat-deco{position:absolute;right:12px;top:14px;font-size:2.2rem;opacity:.07;}

/* ═══ SUBSCRIPTION BANNER ═══════════════════════════════════ */
.sub-banner{background:linear-gradient(135deg,var(--dark) 0%,var(--dark2) 100%);border-radius:var(--radius);padding:18px 22px;margin-bottom:22px;display:flex;align-items:center;justify-content:space-between;gap:16px;position:relative;overflow:hidden;flex-wrap:wrap;}
.sub-banner::after{content:'⭐';position:absolute;right:-10px;top:-10px;font-size:100px;opacity:.04;}
.sub-info{display:flex;align-items:center;gap:14px;flex-wrap:wrap;}
.sub-plan-badge{background:linear-gradient(90deg,var(--gold),#ffb300);color:var(--dark);font-size:.72rem;font-weight:800;padding:4px 14px;border-radius:50px;letter-spacing:.5px;white-space:nowrap;}
.sub-text h4{color:white;font-size:.9rem;font-weight:700;margin-bottom:3px;}
.sub-text p{color:rgba(255,255,255,.55);font-size:.75rem;}
.sub-progress{flex:1;max-width:220px;}
.sub-progress-label{display:flex;justify-content:space-between;font-size:.7rem;color:rgba(255,255,255,.5);margin-bottom:5px;}
.sub-progress-label span:last-child{color:var(--gold);font-weight:700;}
.sub-bar{height:6px;background:rgba(255,255,255,.1);border-radius:3px;overflow:hidden;}
.sub-fill{height:100%;background:linear-gradient(90deg,var(--gold),#ffb300);border-radius:3px;}
@media(max-width:600px){.sub-progress{display:none;}}

/* ═══ CARDS ═════════════════════════════════════════════════ */
.card{background:white;border-radius:var(--radius);border:1.5px solid #f0e6ef;overflow:hidden;margin-bottom:20px;}
.card-head{padding:14px 18px;border-bottom:1px solid #f8f0f7;display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:8px;}
.card-title{font-weight:700;font-size:.92rem;color:var(--dark);display:flex;align-items:center;gap:8px;}
.card-body{padding:18px;}
.card-body.no-pad{padding:0;}

/* ═══ MINI BAR CHART ════════════════════════════════════════ */
.bar-chart{display:flex;align-items:flex-end;gap:8px;height:80px;padding:0 4px;}
.bar-col{display:flex;flex-direction:column;align-items:center;gap:4px;flex:1;}
.bar{width:100%;border-radius:4px 4px 0 0;background:linear-gradient(180deg,var(--pink-main),var(--pink-deep));transition:height .6s ease;min-height:4px;}
.bar.blue{background:linear-gradient(180deg,var(--blue-light),var(--blue-main));}
.bar-label{font-size:.6rem;color:var(--gray);white-space:nowrap;}
.chart-legend{display:flex;gap:16px;margin-top:10px;justify-content:center;}
.legend-item{display:flex;align-items:center;gap:5px;font-size:.72rem;color:var(--gray);}
.legend-dot{width:8px;height:8px;border-radius:50%;flex-shrink:0;}

/* ═══ TABLES ════════════════════════════════════════════════ */
.data-table{width:100%;border-collapse:collapse;font-size:.8rem;}
.data-table th{background:var(--pink-pale);color:var(--pink-deep);font-weight:700;padding:10px 14px;text-align:left;font-size:.72rem;text-transform:uppercase;letter-spacing:.5px;white-space:nowrap;}
.data-table td{padding:11px 14px;border-bottom:1px solid #fdf0f7;vertical-align:middle;}
.data-table tr:last-child td{border-bottom:none;}
.data-table tr:hover td{background:#fdf8fc;}
.table-wrap{overflow-x:auto;}

/* ═══ STATUS BADGES ═════════════════════════════════════════ */
.status{display:inline-flex;align-items:center;gap:5px;padding:3px 10px;border-radius:50px;font-size:.7rem;font-weight:700;white-space:nowrap;}
.status::before{content:'';width:6px;height:6px;border-radius:50%;flex-shrink:0;}
.status.placed,.status.pending{background:var(--orange-pale);color:var(--orange);}
.status.placed::before,.status.pending::before{background:var(--orange);}
.status.processing{background:var(--blue-pale);color:var(--blue-main);}
.status.processing::before{background:var(--blue-main);}
.status.shipped{background:#e8f5e9;color:#1b5e20;}
.status.shipped::before{background:#2e7d32;}
.status.delivered{background:var(--green-pale);color:var(--green);}
.status.delivered::before{background:var(--green);}
.status.cancelled,.status.inactive{background:var(--red-pale);color:var(--red);}
.status.cancelled::before,.status.inactive::before{background:var(--red);}
.status.active{background:var(--green-pale);color:var(--green);}
.status.active::before{background:var(--green);}

/* ═══ PRODUCT THUMB ═════════════════════════════════════════ */
.prod-thumb{display:flex;align-items:center;gap:10px;}
.prod-thumb-img{width:38px;height:38px;border-radius:8px;background:var(--pink-pale);flex-shrink:0;display:flex;align-items:center;justify-content:center;font-size:1rem;overflow:hidden;border:1px solid #f0e6ef;}
.prod-thumb-img img{width:100%;height:100%;object-fit:cover;}
.prod-thumb-name{font-size:.82rem;font-weight:700;color:var(--dark);max-width:160px;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;}
.prod-thumb-sku{font-size:.68rem;color:var(--gray);}

/* ═══ FORMS ═════════════════════════════════════════════════ */
.form-grid{display:grid;grid-template-columns:1fr 1fr;gap:14px;}
@media(max-width:600px){.form-grid{grid-template-columns:1fr;}}
.form-group{display:flex;flex-direction:column;gap:5px;}
.form-group.full{grid-column:1/-1;}
.form-label{font-size:.78rem;font-weight:700;color:var(--dark);}
.req{color:var(--pink-main);}
.gst-note{margin-top:12px;background:var(--blue-pale,#e3f2fd);border:1px solid #bbdefb;border-radius:9px;padding:10px 14px;font-size:.78rem;color:var(--blue-main,#1565c0);line-height:1.6;}
.form-input,.form-select,.form-textarea{border:1.5px solid #e8dff0;border-radius:9px;padding:9px 12px;font-size:.85rem;font-family:'Plus Jakarta Sans',sans-serif;outline:none;transition:border-color var(--transition);width:100%;background:white;color:var(--dark);}
.form-input:focus,.form-select:focus,.form-textarea:focus{border-color:var(--pink-main);box-shadow:0 0 0 3px rgba(233,30,140,.08);}
.form-textarea{resize:vertical;min-height:90px;}
.form-hint{font-size:.68rem;color:var(--gray);}
.upload-zone{border:2px dashed #e0c8e8;border-radius:11px;padding:28px;text-align:center;cursor:pointer;transition:all var(--transition);background:var(--pink-pale);}
.upload-zone:hover{border-color:var(--pink-main);background:var(--pink-soft);}
.upload-zone .up-icon{font-size:2.2rem;margin-bottom:8px;}
.upload-zone h4{font-size:.85rem;font-weight:700;color:var(--dark);margin-bottom:4px;}
.upload-zone p{font-size:.72rem;color:var(--gray);}

/* ═══ EARNINGS ══════════════════════════════════════════════ */
.earn-period-tabs{display:flex;gap:6px;flex-wrap:wrap;}
.period-tab{padding:5px 14px;border-radius:50px;border:1.5px solid #e8e0ef;background:white;font-size:.75rem;font-weight:600;cursor:pointer;color:var(--gray);font-family:'Plus Jakarta Sans',sans-serif;transition:all var(--transition);}
.period-tab.active,.period-tab:hover{background:var(--pink-main);color:white;border-color:var(--pink-main);}
.earn-summary{display:grid;grid-template-columns:repeat(3,1fr);gap:12px;margin-bottom:18px;}
@media(max-width:600px){.earn-summary{grid-template-columns:1fr;}}
.earn-box{background:white;border-radius:11px;padding:16px;border:1.5px solid #f0e6ef;text-align:center;}
.earn-box .e-label{font-size:.7rem;color:var(--gray);font-weight:700;text-transform:uppercase;letter-spacing:.5px;margin-bottom:6px;}
.earn-box .e-val{font-family:'Sora',sans-serif;font-size:1.4rem;font-weight:700;}
.earn-box .e-val.green{color:var(--green);}
.earn-box .e-val.pink{color:var(--pink-deep);}
.earn-box .e-val.blue{color:var(--blue-main);}

/* ═══ FILTER BAR ════════════════════════════════════════════ */
.filter-bar{display:flex;gap:10px;align-items:center;flex-wrap:wrap;margin-bottom:16px;}
.filter-search{flex:1;min-width:180px;display:flex;align-items:center;background:white;border:1.5px solid #e8e0ef;border-radius:9px;padding:0 12px;gap:8px;transition:border-color var(--transition);}
.filter-search:focus-within{border-color:var(--pink-main);}
.filter-search input{border:none;outline:none;font-size:.82rem;font-family:'Plus Jakarta Sans',sans-serif;padding:8px 0;flex:1;}
.filter-select{border:1.5px solid #e8e0ef;border-radius:9px;padding:8px 12px;font-size:.8rem;font-family:'Plus Jakarta Sans',sans-serif;color:var(--dark);outline:none;background:white;cursor:pointer;transition:border-color var(--transition);}
.filter-select:focus{border-color:var(--pink-main);}

/* ═══ SUBSCRIPTION PLANS ════════════════════════════════════ */
.plans-grid{display:grid;grid-template-columns:repeat(3,1fr);gap:14px;}
@media(max-width:700px){.plans-grid{grid-template-columns:1fr;}}
.plan-card{border-radius:var(--radius);border:2px solid #f0e6ef;padding:22px;text-align:center;transition:all var(--transition);position:relative;overflow:hidden;}
.plan-card.featured{border-color:var(--gold);background:linear-gradient(135deg,#fffbeb,#fff8e1);}
.plan-card.featured::before{content:'POPULAR';position:absolute;top:12px;right:-20px;background:var(--gold);color:var(--dark);font-size:.6rem;font-weight:800;padding:3px 30px;transform:rotate(45deg);letter-spacing:.5px;}
.plan-card.active-plan{border-color:var(--green);background:var(--green-pale);}
.plan-name{font-family:'Sora',sans-serif;font-size:1.1rem;font-weight:700;color:var(--dark);margin-bottom:4px;}
.plan-price{font-family:'Sora',sans-serif;font-size:1.8rem;font-weight:700;color:var(--pink-deep);margin-bottom:4px;}
.plan-price sup{font-size:1rem;}
.plan-price small{font-size:.8rem;color:var(--gray);}
.plan-features{list-style:none;text-align:left;margin:14px 0;font-size:.78rem;}
.plan-features li{padding:4px 0;color:var(--gray);display:flex;align-items:center;gap:7px;}
.plan-features li::before{content:'✓';color:var(--green);font-weight:700;flex-shrink:0;}
.btn-plan{width:100%;padding:10px;border-radius:9px;font-size:.82rem;font-weight:700;cursor:pointer;font-family:'Plus Jakarta Sans',sans-serif;transition:all var(--transition);border:none;}
.btn-plan.pink{background:linear-gradient(90deg,var(--pink-main),var(--pink-deep));color:white;}
.btn-plan.gold{background:linear-gradient(90deg,var(--gold),#ffb300);color:var(--dark);}
.btn-plan.current{background:var(--green);color:white;cursor:default;}

/* ═══ EMPTY STATE ═══════════════════════════════════════════ */
.empty-state{text-align:center;padding:40px 20px;color:var(--gray);}
.empty-state .es-icon{font-size:3rem;margin-bottom:12px;}
.empty-state h3{font-size:1rem;color:var(--dark);margin-bottom:6px;}
.empty-state p{font-size:.82rem;margin-bottom:16px;}

/* ═══ PAGINATION ════════════════════════════════════════════ */
.pg-bar{display:flex;justify-content:space-between;align-items:center;margin-top:12px;font-size:.8rem;color:var(--gray);}
.pg-btns{display:flex;gap:6px;}

/* ═══ TOAST ═════════════════════════════════════════════════ */
.toast{position:fixed;bottom:24px;right:24px;background:var(--dark);color:white;padding:12px 20px;border-radius:12px;font-size:.85rem;z-index:9999;box-shadow:0 8px 24px rgba(0,0,0,.25);transform:translateY(20px);opacity:0;transition:all .3s ease;pointer-events:none;border-left:4px solid var(--pink-main);max-width:300px;}
.toast.show{transform:translateY(0);opacity:1;}

/* ═══ NOTIF PANEL ═══════════════════════════════════════════ */
.notif-panel{display:none;position:fixed;top:calc(var(--header-h) + 6px);right:16px;width:300px;background:white;border-radius:var(--radius);box-shadow:0 12px 40px rgba(0,0,0,.15);z-index:2000;border:1px solid #f0e6ef;overflow:hidden;}
.notif-panel.open{display:block;animation:dropIn .2s ease;}
@keyframes dropIn{from{opacity:0;transform:translateY(-8px)}to{opacity:1;transform:translateY(0)}}
.notif-head{padding:12px 16px;background:var(--pink-pale);border-bottom:1px solid #f0e6ef;font-weight:700;font-size:.85rem;display:flex;justify-content:space-between;}
.notif-item{display:flex;gap:10px;padding:11px 16px;border-bottom:1px solid #fdf8fc;}
.notif-item:last-child{border-bottom:none;}
.notif-dot-item{width:8px;height:8px;border-radius:50%;background:var(--pink-main);flex-shrink:0;margin-top:5px;}
.notif-item h5{font-size:.78rem;font-weight:600;margin-bottom:2px;}
.notif-item p{font-size:.7rem;color:var(--gray);}

/* ═══ MOBILE BOTTOM NAV ═════════════════════════════════════ */
.mob-bottom-nav{display:none;position:fixed;bottom:0;left:0;right:0;z-index:700;background:white;border-top:1.5px solid #f0e6ef;box-shadow:0 -4px 20px rgba(194,24,91,.1);padding:6px 0;}
@media(max-width:900px){.mob-bottom-nav{display:flex;}}
.mob-bottom-inner{display:flex;justify-content:space-around;width:100%;}
.mob-bottom-btn{display:flex;flex-direction:column;align-items:center;gap:2px;font-size:.58rem;font-weight:600;color:var(--gray);cursor:pointer;padding:4px 8px;border:none;background:transparent;font-family:'Plus Jakarta Sans',sans-serif;transition:color var(--transition);text-decoration:none;}
.mob-bottom-btn .bi{font-size:1.25rem;}
.mob-bottom-btn.active,.mob-bottom-btn:hover{color:var(--pink-main);}
@media(max-width:900px){body{padding-bottom:70px;}}
</style>
</head>
<body>
<?php
$v   = $vendor;
$vp  = $vendorProfile ?? [];
$sec = $section ?? 'overview';
$vid = \App\Core\Auth::vendorId();
$planName = $vp['plan_name'] ?? 'Free Plan';
?>

<!-- TOP BAR -->
<div class="top-bar">
  <div class="top-bar-left">
    <button class="sidebar-toggle" id="sidebarToggle" onclick="toggleSidebar()">☰</button>
    <a href="<?= APP_URL ?>" class="top-logo">Namma <span>E</span> Store</a>
    <span class="vendor-badge-top">🏪 Vendor Panel</span>
  </div>
  <div class="top-bar-right">
    <span class="top-vendor-name"><?= e($vp['shop_name'] ?? $v['name']) ?></span>
    <button class="top-icon-btn" onclick="toggleNotif()" title="Notifications">🔔<span class="notif-dot"></span></button>
    <a href="<?= APP_URL ?>" class="top-icon-btn" title="View Store">🏬</a>
    <div class="vendor-avatar" title="My Account"><?= strtoupper(substr($v['name'],0,1)) ?></div>
  </div>
</div>

<!-- NOTIFICATION PANEL -->
<div class="notif-panel" id="notifPanel">
  <div class="notif-head"><span>🔔 Notifications</span><span style="color:var(--pink-main);font-size:.75rem;cursor:pointer;">Mark all read</span></div>
  <?php if(is_array($stats) && !empty($stats['pending'])): ?>
  <div class="notif-item"><div class="notif-dot-item"></div><div><h5>📦 <?= $stats['pending'] ?> order<?= $stats['pending']>1?'s':'' ?> awaiting action</h5><p>Review and process your pending orders</p></div></div>
  <?php endif; ?>
  <div class="notif-item" style="opacity:.7"><div class="notif-dot-item" style="background:var(--gray)"></div><div><h5>Welcome to Namma E Store Seller Panel! 🎉</h5><p>Complete your shop profile to attract buyers.</p></div></div>
</div>

<div class="dash-layout">
  <div class="sidebar-overlay" id="sidebarOverlay" onclick="closeSidebar()"></div>

  <!-- SIDEBAR -->
  <aside class="sidebar" id="sidebar">
    <div class="vendor-card-side">
      <div class="vendor-ava-big"><?= strtoupper(substr($v['name'],0,1)) ?></div>
      <h3><?= e($vp['shop_name'] ?? $v['name']) ?></h3>
      <p><?= e($v['email']) ?></p>
      <span class="plan-badge">⭐ <?= e($planName) ?></span>
    </div>
    <div class="nav-section">
      <div class="nav-section-label">Main</div>
      <a href="<?= VENDOR_URL ?>/dashboard" class="nav-item <?= $sec==='overview'?'active':'' ?>"><span class="nav-icon">📊</span><span class="nav-label">Overview</span></a>
      <a href="<?= VENDOR_URL ?>/orders" class="nav-item <?= $sec==='orders'||$sec==='order-detail'?'active':'' ?>"><span class="nav-icon">📦</span><span class="nav-label">Orders</span><?php if(is_array($stats) && !empty($stats['pending'])): ?><span class="nav-count"><?= $stats['pending'] ?></span><?php endif; ?></a>
      <a href="<?= VENDOR_URL ?>/products" class="nav-item <?= $sec==='products'?'active':'' ?>"><span class="nav-icon">🛍️</span><span class="nav-label">Products</span></a>
      <a href="<?= VENDOR_URL ?>/products/add" class="nav-item <?= $sec==='add-product'?'active':'' ?>"><span class="nav-icon">➕</span><span class="nav-label">Add Product</span></a>
    </div>
    <div class="sidebar-divider"></div>
    <div class="nav-section">
      <div class="nav-section-label">Finance</div>
      <a href="<?= VENDOR_URL ?>/earnings?section=earnings" class="nav-item <?= $sec==='earnings'?'active':'' ?>"><span class="nav-icon">💰</span><span class="nav-label">Earnings</span></a>
      <a href="<?= VENDOR_URL ?>/commission?section=commission" class="nav-item <?= $sec==='commission'?'active':'' ?>"><span class="nav-icon">📈</span><span class="nav-label">Commission</span></a>
      <a href="<?= VENDOR_URL ?>/subscription?section=subscription" class="nav-item <?= $sec==='subscription'?'active':'' ?>"><span class="nav-icon">⭐</span><span class="nav-label">Subscription</span><span class="nav-count gold"><?= strlen($planName)>5?substr($planName,0,5).'…':$planName ?></span></a>
      <a href="<?= VENDOR_URL ?>/wallet" class="nav-item <?= $sec==='wallet'?'active':'' ?>"><span class="nav-icon">👛</span><span class="nav-label">Wallet & Payouts</span></a>
      <a href="<?= VENDOR_URL ?>/returns" class="nav-item <?= $sec==='returns'?'active':'' ?>"><span class="nav-icon">↩️</span><span class="nav-label">Returns</span></a>
      <a href="<?= VENDOR_URL ?>/invoices" class="nav-item <?= $sec==='invoices'?'active':'' ?>"><span class="nav-icon">🧾</span><span class="nav-label">GST Invoices</span></a>
      <a href="<?= VENDOR_URL ?>/notifications" class="nav-item <?= $sec==='notifications'?'active':'' ?>"><span class="nav-icon">🔔</span><span class="nav-label">Notifications</span></a>
    </div>
    <div class="sidebar-divider"></div>
    <div class="nav-section">
      <div class="nav-section-label">Manage</div>
      <a href="<?= VENDOR_URL ?>/reviews?section=reviews" class="nav-item <?= $sec==='reviews'?'active':'' ?>"><span class="nav-icon">⭐</span><span class="nav-label">Reviews</span></a>
      <a href="<?= VENDOR_URL ?>/settings" class="nav-item <?= $sec==='settings'?'active':'' ?>"><span class="nav-icon">⚙️</span><span class="nav-label">Settings</span></a>
    </div>
    <div class="sidebar-divider"></div>
    <div class="nav-section">
      <a href="<?= APP_URL ?>" class="nav-item"><span class="nav-icon">🏬</span><span class="nav-label">View My Store</span></a>
      <a href="<?= VENDOR_URL ?>/logout" class="nav-item" style="color:rgba(255,100,100,.7);"><span class="nav-icon">🚪</span><span class="nav-label">Logout</span></a>
    </div>
  </aside>

  <!-- MAIN CONTENT -->
  <main class="main-content">

    <?php if(!empty($_SESSION['flash'])): $fl=$_SESSION['flash']; unset($_SESSION['flash']); ?>
    <div class="flash-alert <?= e($fl['type']) ?>"><?= e($fl['message']) ?></div>
    <?php endif; ?>

    <?= $content ?>

  </main>
</div>

<!-- MOBILE BOTTOM NAV -->
<nav class="mob-bottom-nav">
  <div class="mob-bottom-inner">
    <a href="<?= VENDOR_URL ?>/dashboard" class="mob-bottom-btn <?= $sec==='overview'?'active':'' ?>"><span class="bi">📊</span>Overview</a>
    <a href="<?= VENDOR_URL ?>/orders" class="mob-bottom-btn <?= $sec==='orders'?'active':'' ?>"><span class="bi">📦</span>Orders</a>
    <a href="<?= VENDOR_URL ?>/products/add" class="mob-bottom-btn <?= $sec==='add-product'?'active':'' ?>"><span class="bi">➕</span>Add</a>
    <a href="<?= VENDOR_URL ?>/products" class="mob-bottom-btn <?= $sec==='products'?'active':'' ?>"><span class="bi">🛍️</span>Products</a>
    <a href="<?= VENDOR_URL ?>/settings" class="mob-bottom-btn <?= $sec==='settings'?'active':'' ?>"><span class="bi">⚙️</span>Settings</a>
  </div>
</nav>

<div class="toast" id="toast"></div>

<script>
const VENDOR_URL  = '<?= VENDOR_URL ?>';
const APP_URL     = '<?= APP_URL ?>';
const CSRF_TOKEN  = '<?= csrf_token() ?>';
const APP_URL    = '<?= APP_URL ?>';

function toggleSidebar(){
  document.getElementById('sidebar').classList.toggle('open');
  document.getElementById('sidebarOverlay').classList.toggle('open');
}
function closeSidebar(){
  document.getElementById('sidebar').classList.remove('open');
  document.getElementById('sidebarOverlay').classList.remove('open');
}
function toggleNotif(){
  document.getElementById('notifPanel').classList.toggle('open');
}
document.addEventListener('click',function(e){
  const p=document.getElementById('notifPanel');
  if(p&&p.classList.contains('open')&&!p.contains(e.target)&&!e.target.closest('.top-icon-btn')) p.classList.remove('open');
});
let toastTimer;
function showToast(msg){
  const t=document.getElementById('toast');
  t.innerHTML=msg; t.classList.add('show');
  clearTimeout(toastTimer);
  toastTimer=setTimeout(()=>t.classList.remove('show'),2800);
}
function confirmDelete(url, msg){
  if(!confirm(msg||'Are you sure?')) return;
  fetch(url,{method:'POST',body:new URLSearchParams({_csrf_token:CSRF_TOKEN})}).then(r=>r.json()).then(d=>{if(d.success)location.reload();else showToast('⚠️ '+d.message);});
}
function toggleProductStatus(id){
  fetch(VENDOR_URL+'/products/'+id+'/toggle',{method:'POST',body:new URLSearchParams({_csrf_token:CSRF_TOKEN})}).then(r=>r.json()).then(d=>{
    if(d.success){location.reload();}
  });
}
</script>
<?= $scripts ?? '' ?>
</body>
</html>
