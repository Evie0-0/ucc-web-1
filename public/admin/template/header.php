<header class="top-header">
	<div class="header-left">
		<h1 id="pageTitle"><?= e($pageTitle ?? '') ?></h1>
	</div>

	<div class="header-right">
<button
	type="button"
	onclick="window.location.href='/user/homepage.html'"
	class="visit-website"
	onmouseover="this.style.transform='translateY(-2px)';this.style.background='linear-gradient(135deg,#00734a,#004d32)';this.style.borderColor='#f2ca68';this.style.boxShadow='0 12px 28px rgba(0,66,43,.28),0 0 0 4px rgba(216,176,86,.10)'"
	onmouseout="this.style.transform='translateY(0)';this.style.background='linear-gradient(135deg,#006a42,#00422b)';this.style.borderColor='#d8b056';this.style.boxShadow='0 8px 20px rgba(0,66,43,.20)'"
	style="
		display:flex;
		align-items:center;
		justify-content:center;
		gap:10px;
		height:48px;
		min-width:160px;
		padding:0 20px;
		border:1px solid #d8b056;
		border-radius:14px;
		background:linear-gradient(135deg,#006a42,#00422b);
		color:#ffffff;
		font-family:'DM Sans',sans-serif;
		font-size:13px;
		font-weight:600;
		cursor:pointer;
		box-shadow:0 8px 20px rgba(0,66,43,.20);
		transition:all .25s ease;
	"
>
	<i
		data-lucide="external-link"
		style="
			width:17px;
			height:17px;
			color:#f2ca68;
			stroke:#f2ca68;
			stroke-width:2;
			flex-shrink:0;
		"
	></i>

			<span
				style="
					color:#ffffff;
					white-space:nowrap;
				"
			>Visit Website</span>
		</button>

		<div class="admin-profile">
			<div class="admin-text">
				<small><?= e($_SESSION['role'] ?? '') ?></small>
				<span>Welcome, <?= e($_SESSION['username'] ?? '') ?>!</span>
			</div>

			<div class="avatar">
				<i data-lucide="circle-user-round"></i>
			</div>
		</div>
	</div>

	<div class="header-art"></div>

	<div class="gold-divider">
		<span></span>
	</div>

</header>