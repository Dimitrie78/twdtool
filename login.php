<div class="modal-dialog">
		<div class="modal-content">
			<div class="modal-heading">
				<h2 class="text-center">Login TWD Statistiktool</h2>
			</div>
			<hr />
			<div class="modal-body">
				<form action="index.php" method = "post" role="form">
					<div class="form-group">
						<div class="input-group">
							<span class="input-group-addon">
							<span class="fas fa-user"></span>
							</span>
							<input name = "loginname" type="text" class="form-control" placeholder="User Name" required autofocus />
						</div>
					</div>
					<div class="form-group">
						<div class="input-group">
							<span class="input-group-addon">
							<span class="fas fa-lock"></span>
							</span>
							<input name="loginpasswort" type="password" class="form-control" placeholder="Password" required/>

						</div>

					</div>

					<div class="form-group text-center">
						<button type="submit" class="btn btn-success btn-lg">Login</button>
					</div>

				</form>
					<?php if(isset($fail)) echo $fail; ?>
			</div>
		</div>
	</div>