<?php
/**
 * Create account page template
 */

$admin_email = get_option( 'admin_email' );
$user        = get_user_by( 'email', $admin_email );

if ( empty( $user ) ) {
 	$user_id = get_current_user_id();
	$user    = get_user_by( 'id', $user_id );
}

?>
<div class="mailchimp-sf-create-account">
	<div class="mailchimp-sf-create-account__header flex items-center">
		<div class="flex items-center">
			<svg width="44" height="44" viewBox="0 0 44 44" fill="none" xmlns="http://www.w3.org/2000/svg">
				<path fill-rule="evenodd" clip-rule="evenodd" d="M38.8997 26.7611C39.4575 26.7611 40.3334 27.4064 40.3334 28.9636C40.3334 30.5129 39.6937 32.2681 39.543 32.6577C37.2384 38.2081 31.7384 41.2979 25.1865 41.1024C19.0784 40.92 13.8691 37.6806 11.5892 32.4001C10.2105 32.4013 8.78979 31.7924 7.7092 30.8302C6.57099 29.8167 5.86866 28.505 5.73182 27.1366C5.62525 26.0713 5.75567 25.0807 6.08784 24.2159L4.80744 23.1258C-1.0514 18.154 17.2735 -2.31728 23.1346 2.82061C23.1641 2.84652 25.1284 4.78347 25.1332 4.78822C25.141 4.78434 26.2169 4.3258 26.2224 4.32349C31.363 2.18416 35.5344 3.21711 35.5395 6.63077C35.5422 8.40595 34.4168 10.475 32.6138 12.3535C33.2671 12.9629 33.7905 13.9154 34.091 15.0039C34.343 15.8082 34.3876 16.6247 34.4105 17.1477C34.4419 17.8711 34.4765 19.5564 34.48 19.5865C34.5258 19.6005 35.0462 19.744 35.204 19.7885C36.5838 20.1779 37.5656 20.6966 38.0457 21.2031C38.5257 21.7096 38.7638 22.1995 38.8503 22.7746C38.9312 23.2383 38.9203 24.0574 38.3117 24.9732C38.3117 24.9732 38.4688 25.3174 38.6205 25.8063C38.7723 26.2952 38.8826 26.7009 38.8997 26.7611ZM24.664 29.3288C24.6649 29.3307 24.6658 29.3326 24.6667 29.3345C24.666 29.3332 24.6657 29.3317 24.665 29.3307C24.6648 29.3301 24.6643 29.3293 24.664 29.3288ZM38.9666 29.4883C39.1063 28.561 38.9017 28.2024 38.6227 28.0298C38.3276 27.8472 37.9743 27.9108 37.9743 27.9108C37.9743 27.9108 37.8132 26.8019 37.3567 25.7944C36.0038 26.8677 34.2618 27.6218 32.9355 28.0045C31.405 28.4459 29.334 28.7852 27.0232 28.6468C25.7412 28.5428 24.893 28.1666 24.574 29.2085C27.5025 30.286 30.6025 29.8247 30.6025 29.8247C30.6625 29.8186 30.716 29.8625 30.7222 29.9228C30.7271 29.9719 30.6974 30.0213 30.6545 30.0395C30.6545 30.0395 28.2733 31.1507 24.4915 29.9752C24.5966 30.8666 25.461 31.2663 25.8741 31.4277C26.3939 31.631 26.9633 31.7251 26.9633 31.7251C31.6493 32.5352 36.0304 29.8424 37.0166 29.1641C37.0906 29.1133 37.1396 29.1628 37.0803 29.2528C37.0233 29.3392 37.0198 29.3444 36.9836 29.3912C35.7765 30.9562 32.531 32.7685 28.3087 32.7678C26.4671 32.7676 24.6265 32.1155 23.9508 31.1135C22.9023 29.559 23.8989 27.2898 25.6459 27.5264C25.648 27.5266 26.2435 27.5945 26.4111 27.6132C28.5951 27.8577 31.7561 27.5499 34.3617 26.3344C36.7454 25.2225 37.6459 23.9987 37.5107 23.008C37.474 22.7412 37.3553 22.4613 37.0999 22.198C36.6716 21.7766 35.9922 21.4485 34.8463 21.1252C34.4677 21.0184 34.2108 20.9501 33.9341 20.8586C33.4421 20.6958 33.1989 20.5646 33.1439 19.6347C33.1197 19.2281 33.0492 17.8115 33.0238 17.2259C32.9789 16.2002 32.856 14.798 31.9904 14.219C31.7648 14.0682 31.514 13.9954 31.2503 13.9813C30.9921 13.9691 30.8628 14.0156 30.8109 14.0245C30.3168 14.1086 30.0248 14.373 29.6605 14.6782C28.5807 15.5824 27.6689 15.7305 26.6552 15.6865C26.0493 15.6611 25.4074 15.5662 24.6715 15.5225C24.5283 15.514 24.3847 15.505 24.2412 15.4976C22.5431 15.4102 20.7219 16.8843 20.4193 18.9773C19.9979 21.8906 22.0972 23.3961 22.7032 24.2795C22.7807 24.3851 22.87 24.5342 22.87 24.6755C22.87 24.8445 22.7611 24.9788 22.6542 25.0928C22.6541 25.093 22.6545 25.0935 22.6543 25.0937C20.9221 26.8839 20.3679 29.7282 21.0206 32.0988C21.1022 32.395 21.2056 32.6781 21.3278 32.9486C22.8596 36.5461 27.6113 38.2214 32.2535 36.6976C32.8754 36.4934 33.4643 36.2408 34.0172 35.9489C35.0584 35.4363 35.9684 34.7312 36.7179 33.9203C37.9494 32.6268 38.6821 31.2208 38.9666 29.4883ZM31.2478 20.4819C31.0319 20.2047 30.8381 19.7571 30.7292 19.2335C30.5357 18.3027 30.5557 17.6284 31.0968 17.5407C31.6381 17.453 31.9 18.0163 32.0934 18.947C32.2236 19.5732 32.1988 20.1483 32.054 20.482C31.804 20.4464 31.5319 20.4478 31.2478 20.4819ZM26.6018 21.2185C26.2146 21.0477 25.7118 20.857 25.1049 20.8944C24.2452 20.9473 23.4988 21.3265 23.2852 21.3019C23.1942 21.2891 23.1555 21.25 23.1441 21.1981C23.1089 21.0377 23.3545 20.7735 23.6136 20.5827C24.3983 20.016 25.4154 19.8933 26.2683 20.2623C26.6855 20.4408 27.0786 20.7588 27.2697 21.0723C27.3621 21.2241 27.38 21.3418 27.3201 21.4035C27.2269 21.5024 26.9891 21.3893 26.6018 21.2185ZM25.8229 21.6651C26.5174 21.5823 27.0266 21.9078 27.1448 22.1004C27.1955 22.1831 27.1756 22.2374 27.1592 22.2625C27.1035 22.3498 26.9838 22.334 26.7303 22.3054C26.2714 22.2528 25.8085 22.2222 25.1067 22.4748C25.1067 22.4748 24.8515 22.5776 24.7376 22.5776C24.7049 22.5776 24.6776 22.5663 24.6547 22.5459C24.6378 22.5316 24.6165 22.5031 24.616 22.4536C24.6152 22.3495 24.7094 22.2016 24.8619 22.0677C25.0404 21.9122 25.3186 21.7451 25.8229 21.6651ZM29.6789 23.3048C29.3365 23.1358 29.1584 22.7952 29.2811 22.5443C29.4038 22.2933 29.7808 22.2269 30.1233 22.396C30.4657 22.5651 30.6438 22.9056 30.5211 23.1566C30.3984 23.4076 30.0213 23.4739 29.6789 23.3048ZM31.8804 21.3715C32.1586 21.3763 32.3789 21.6909 32.3724 22.0744C32.3659 22.4578 32.1352 22.7647 31.857 22.76C31.5788 22.7552 31.3585 22.4405 31.365 22.0572C31.3715 21.6738 31.6022 21.3668 31.8804 21.3715ZM17.4294 12.9837C17.3781 13.0432 17.4543 13.1274 17.5177 13.0811C18.7724 12.1639 20.4922 11.3111 22.7455 10.7587C25.2695 10.14 27.6992 10.3994 29.1837 10.7413C29.2579 10.7585 29.3053 10.6292 29.2387 10.5918C28.2577 10.0389 26.7525 9.66326 25.6848 9.65568C25.6321 9.65531 25.6025 9.59415 25.6339 9.55164C25.8186 9.30212 26.0716 9.05542 26.3028 8.8772C26.3548 8.83707 26.3233 8.75294 26.2579 8.75695C24.7373 8.85097 23.0037 9.5825 22.0071 10.2653C21.958 10.299 21.8944 10.2548 21.9066 10.1963C21.9842 9.82013 22.2291 9.32453 22.3562 9.09304C22.3864 9.03833 22.3275 8.97786 22.2721 9.00634C20.6703 9.82989 18.882 11.2967 17.4294 12.9837ZM9.88291 21.0069C11.5574 16.4908 14.3526 12.3289 18.0529 9.46532C20.799 7.16349 23.7603 5.51212 23.7603 5.51212C23.7603 5.51212 22.1653 3.65336 21.6837 3.5164C18.7212 2.71168 12.3216 7.14734 8.2357 13.0076C6.58239 15.3788 4.21566 19.5777 5.34733 21.7374C5.48672 22.0052 6.27601 22.6927 6.69974 23.0479C7.40848 22.013 8.56768 21.2639 9.88291 21.0069ZM12.0929 30.9171C14.2342 30.5501 14.7948 28.2131 14.4418 25.9178C14.0433 23.3262 12.299 22.4127 11.1152 22.3475C10.7861 22.3303 10.4809 22.3599 10.2283 22.411C8.11561 22.8392 6.92241 24.6456 7.15716 26.9926C7.36956 29.1161 9.50714 30.9065 11.486 30.9599C11.6907 30.9648 11.8939 30.9512 12.0929 30.9171ZM12.9025 28.2499C13.0125 28.2241 13.1262 28.1975 13.1954 28.2847C13.2207 28.3131 13.2598 28.3792 13.2134 28.4878C13.1347 28.6715 12.8233 28.9227 12.378 28.9059C11.9199 28.8703 11.4101 28.5358 11.3412 27.7027C11.3071 27.2911 11.4617 26.7902 11.5568 26.5282C11.7408 26.0216 11.5748 25.4907 11.1438 25.2072C10.8904 25.0405 10.5879 24.9838 10.2919 25.0475C10.0013 25.11 9.75379 25.2818 9.59483 25.5314C9.46304 25.7383 9.38257 25.9964 9.3394 26.1351C9.32782 26.1723 9.3186 26.2015 9.31162 26.2204C9.21464 26.4826 9.0598 26.5592 8.9551 26.545C8.90552 26.5381 8.83732 26.5049 8.79372 26.3848C8.67469 26.0568 8.77086 25.1283 9.3863 24.4459C9.77634 24.0133 10.3879 23.792 10.9819 23.8676C11.6007 23.9466 12.1153 24.3227 12.4308 24.9265C12.8501 25.729 12.4767 26.571 12.2536 27.074C12.2293 27.1285 12.2072 27.1785 12.1883 27.2235C12.0484 27.5574 12.041 27.849 12.1678 28.0446C12.2651 28.1948 12.4384 28.2827 12.643 28.2866C12.7384 28.2882 12.8255 28.2679 12.9025 28.2499Z" fill="#241C15"/>
			</svg>
			<span class="mailchimp-sf-create-account-plus">+</span>
			<svg version="1.1" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" x="0px" y="0px" width="44" height="44" viewBox="0 0 96.98 96.98" xml:space="preserve">
				<path d="M49.16,51.833L37.694,85.152c3.425,1.004,7.046,1.558,10.798,1.558c4.449,0,8.719-0.77,12.689-2.167
						c-0.102-0.164-0.195-0.338-0.271-0.527L49.16,51.833z"/>
				<path d="M10.272,48.488c0,15.129,8.792,28.202,21.541,34.396l-18.23-49.949C11.463,37.688,10.272,42.948,10.272,48.488z"/>
				<path d="M74.289,46.56c0-4.723-1.695-7.993-3.149-10.541c-1.938-3.148-3.754-5.813-3.754-8.962c0-3.513,2.664-6.783,6.418-6.783
						c0.17,0,0.33,0.021,0.496,0.029c-6.798-6.227-15.856-10.031-25.807-10.031c-13.354,0-25.101,6.85-31.932,17.227
						c0.896,0.027,1.739,0.046,2.459,0.046c3.998,0,10.187-0.485,10.187-0.485c2.062-0.124,2.302,2.903,0.245,3.146
						c0,0-2.071,0.243-4.374,0.365l13.915,41.397l8.363-25.085L41.4,30.57c-2.058-0.122-4.007-0.365-4.007-0.365
						c-2.058-0.12-1.818-3.268,0.241-3.146c0,0,6.313,0.485,10.066,0.485c3.997,0,10.188-0.485,10.188-0.485
						c2.062-0.122,2.303,2.903,0.243,3.146c0,0-2.073,0.243-4.374,0.365L67.57,71.653l3.812-12.738
						C73.033,53.629,74.289,49.831,74.289,46.56z"/>
				<path d="M82.025,30.153c0.164,1.216,0.258,2.525,0.258,3.93c0,3.878-0.723,8.238-2.905,13.689L67.703,81.523
						c11.361-6.626,19.006-18.936,19.006-33.033C86.71,41.844,85.011,35.596,82.025,30.153z"/>
				<path d="M48.49,0C21.71,0,0.001,21.71,0.001,48.49S21.71,96.98,48.49,96.98s48.489-21.71,48.489-48.49S75.27,0,48.49,0z
							M48.492,90.997c-23.44,0-42.507-19.067-42.507-42.509c0-23.438,19.066-42.505,42.507-42.505
						c23.437,0,42.503,19.068,42.503,42.505C90.996,71.928,71.928,90.997,48.492,90.997z"/>
			</svg>
		</div>
		<div class="">
			<h3><?php esc_html_e( 'Mailchimp List Subscribe Form', 'mailchimp' ) ?></h3>
			<div class="flex items-center wizard-steps">
				<div class="current"><?php echo esc_html__( 'Sign up', 'mailchimp' ) ?></div>
				<span class="chevron">
						<svg width="16" height="16" viewBox="0 0 16 16" fill="none" xmlns="http://www.w3.org/2000/svg">
							<path fill-rule="evenodd" clip-rule="evenodd" d="M9.05715 8.00005L6.19522 5.13812L7.13803 4.19531L10.9428 8.00005L7.13803 11.8048L6.19522 10.862L9.05715 8.00005Z" fill="#241C15" fill-opacity="0.3"/>
						</svg>
					</span>
				<div class="deselected"><?php echo esc_html__( 'Activate account', 'mailchimp' ) ?></div>
				<span class="chevron">
						<svg width="16" height="16" viewBox="0 0 16 16" fill="none" xmlns="http://www.w3.org/2000/svg">
							<path fill-rule="evenodd" clip-rule="evenodd" d="M9.05715 8.00005L6.19522 5.13812L7.13803 4.19531L10.9428 8.00005L7.13803 11.8048L6.19522 10.862L9.05715 8.00005Z" fill="#241C15" fill-opacity="0.3"/>
						</svg>
					</span>
				<div class="deselected"><?php echo esc_html__( 'Choose plan', 'mailchimp' ) ?></div>
			</div>
		</div>
	</div>
	<div class="mailchimp-sf-create-account__body">
		<div class="mailchimp-sf-create-account__body-inner">
			<form class="mailchimp-sf-activate-account">
				<div id="mailchimp-sf-profile-details" class="mailchimp-sf-create-account-step">
					<div class="title"><?php esc_html_e( 'Confirm your information', 'mailchimp' ) ?></div>
					<div class="subtitle"><?php esc_html_e( 'Profile details', 'mailchimp' ) ?></div>
					<div class="mailchimp-sf-form-wrapper">
						<fieldset>
							<input id="org" name="org" type="hidden" value="<?php echo esc_attr( get_bloginfo( 'name' ) ); ?>">
							<div class="form-row">
								<div class="box box-half">
									<label for="first_name">
										<span> <?php esc_html_e( 'First name', 'mailchimp' ); ?></span>
									</label>
									<input required type="text" id="first_name" name="first_name" value="<?php echo esc_attr( isset($user->first_name) ? $user->first_name : '' ); ?>"/>
									<p id="mailchimp-sf-first_name-error" class="error-field"></p>
								</div>
								<div class="box box-half">
									<label for="last_name">
										<span> <?php esc_html_e( 'Last name', 'mailchimp' ); ?></span>
									</label>
									<input required type="text" id="last_name" name="last_name" value="<?php echo esc_attr( isset($user->last_name) ? $user->last_name : '' ); ?>"/>
									<p id="mailchimp-sf-last_name-error" class="error-field"></p>
								</div>
							</div>

							<div class="form-row">
								<div class="box box-half">
									<label for="business_name">
										<span><?php esc_html_e( 'Business name', 'mailchimp' ); ?></span>
									</label>
									<input required type="text" id="business_name" name="business_name" value="<?php echo esc_attr( get_bloginfo( 'name' ) ); ?>"/>
									<p id="mailchimp-sf-business_name-error" class="error-field"></p>

									<p><?php esc_html_e( 'You can always change this later in your account settings.', 'mailchimp' ); ?></p>
								</div>
								<div class="box box-half">
									<label for="phone_number" class="optional flex justify-between">
										<span> <?php esc_html_e( 'Phone number', 'mailchimp' ); ?></span>
										<span>Optional</span>
									</label>
									<input type="text" id="phone_number" name="phone_number" value="<?php echo esc_attr( isset($user->billing_phone) ? $user->billing_phone : '' ); ?>"/>
								</div>
							</div>

							<div class="form-row">
								<div class="box">
									<label for="email">
										<span> <?php esc_html_e( 'Email', 'mailchimp' ); ?></span>
									</label>
									<input required type="email" id="email" name="email" value="<?php echo esc_attr( isset($user->user_email) ? $user->user_email : '' ) ?>"/>
									<p id="mailchimp-sf-email-error" class="error-field"></p>

								</div>
							</div>
							<div class="form-row">
								<div class="box">
									<label for="confirm_email">
										<span> <?php esc_html_e( 'Confirm Email', 'mailchimp' ); ?></span>
									</label>
									<input required type="email" id="confirm_email" name="confirm_email"/>
									<p id="mailchimp-sf-confirm_email-error" class="error-field"></p>
								</div>
							</div>
						</fieldset>
					</div>
				</div>

				<div id="mailchimp-sf-business-address" class="mailchimp-sf-create-account-step">
					<div class="subtitle"><?php echo esc_html__( 'Business Address', 'mailchimp' ) ?></div>

					<div class="mailchimp-sf-form-wrapper">
						<fieldset>
							<div class="form-row">
								<div class="box">
									<label for="address">
										<span> <?php esc_html_e( 'Address line 1 (Street address or post office box)', 'mailchimp' ); ?></span>
									</label>
									<input required type="text" id="address" name="address" value=""/>
									<p id="mailchimp-sf-address-error" class="error-field"></p>
								</div>
							</div>

							<div class="form-row">
								<div class="box">
									<label for="address2">
										<span> <?php esc_html_e( 'Address line 2', 'mailchimp' ); ?></span>
									</label>
									<input type="text" id="address2" name="address2" value=""/>
								</div>
							</div>

							<div class="form-row">
								<div class="box box-half">
									<label for="city">
										<span> <?php esc_html_e( 'City', 'mailchimp' ); ?></span>
									</label>
									<input required type="text" id="city" name="city" value=""/>
									<p id="mailchimp-sf-city-error" class="error-field"></p>
								</div>
								<div class="box box-half">
									<label for="state">
										<span> <?php esc_html_e( 'State/Province/Region', 'mailchimp' ); ?></span>
									</label>
									<input required type="text" id="state" name="state" value=""/>
									<p id="mailchimp-sf-state-error" class="error-field"></p>
								</div>
							</div>

							<div class="form-row">
								<div class="box box-half">
									<label for="zip">
										<span> <?php esc_html_e( 'Zip/Postal code', 'mailchimp' ); ?></span>
									</label>
									<input required type="text" id="zip" name="zip" value=""/>
									<p id="mailchimp-sf-zip-error" class="error-field"></p>
								</div>
								<div class="box box-half">
									<label for="country">
										<span> <?php esc_html_e( 'Country', 'mailchimp' ); ?></span>
									</label>
									<div class="mailchimp-select-wrapper">
										<select id="country" name="country" required>
										<?php
										foreach ( $countries as $key => $value ) {
											echo '<option value="' . esc_attr( $key ) . '">' . esc_html( $value ) . '</option>';
										}
										?>
										</select>
									</div>
								</div>
							</div>

							<div class="form-row">
								<div class="box">
									<label for="timezone">
										<span><?php esc_html_e( 'Timezone', 'mailchimp' ); ?></span>
									</label>
									<div class="mailchimp-select-wrapper">
										<select id="timezone" name="timezone" required>
											<?php
											foreach ( $timezones as $timezone ) {
												?>
												<option value="<?php echo esc_attr( $timezone['zone'] ); ?>" <?php selected( $timezone['zone'] === $selected_timezone, true ) ?>>
													<?php echo esc_html( $timezone['diff_from_GMT'] . ' - ' . $timezone['zone'] ); ?>
												</option>
												<?php
											}
											?>
										</select>
									</div>
								</div>
							</div>
						</fieldset>
					</div>
				</div>

				<div class="box terms">
					<p>
						<?php
						echo sprintf(
						/* translators: %s - Mailchimp legal pages */                                wp_kses(
							__( 'By clicking the "Get Started!" button, you are creating a Mailchimp account, and you agree to Mailchimp\'s <a href=%1$s target=_blank>Terms of Use</a> and <a href=%2$s target=_blank>Privacy Policy</a>.', 'mailchimp' ),
							array(
								'a' => array(
									'href'   => array(),
									'target' => '_blank',
								),
							)
						),
							esc_url( 'https://mailchimp.com/legal/terms' ),
							esc_url( 'https://mailchimp.com/legal/privacy' )
						);
						?>
					</p>
				</div>
				<div class="box">
					<button type="submit" id="mailchimp-sf-create-activate-account" class="button button-primary create-account-save">
							<span class="mailchimp-sf-loading hidden">
								<svg class="animate-spin" width="24" height="24" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
										<circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
										<path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
								</svg>
							</span>
							<?php esc_html_e( 'Activate Account', 'mailchimp' ); ?>
					</button>
				</div>
			</form>
		</div>
	</div>
</div>
