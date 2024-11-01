<?php
/**
 * Sports Leagues :: Data.
 *
 * @since   0.1.0
 * @package Sports_Leagues
 */

/**
 * Sports Leagues :: Data class.
 *
 * @since 0.1.0
 */
class Sports_Leagues_Data {

	/**
	 * Parent plugin class.
	 *
	 * @var Sports_Leagues
	 * @since  0.1.0
	 */
	protected $plugin = null;

	/**
	 * Countries data.
	 *
	 * @var    - Array of countries data
	 * @since  0.1.0
	 */
	private $countries;

	/**
	 * Constructor.
	 *
	 * @since  0.1.0
	 *
	 * @param  Sports_Leagues $plugin Main plugin object.
	 */
	public function __construct( $plugin ) {

		$this->plugin = $plugin;

		$this->countries = [
			'af' => esc_html_x( 'Afghanistan', 'country', 'sports-leagues' ),
			'al' => esc_html_x( 'Albania', 'country', 'sports-leagues' ),
			'dz' => esc_html_x( 'Algeria', 'country', 'sports-leagues' ),
			'ds' => esc_html_x( 'American Samoa', 'country', 'sports-leagues' ),
			'ad' => esc_html_x( 'Andorra', 'country', 'sports-leagues' ),
			'ao' => esc_html_x( 'Angola', 'country', 'sports-leagues' ),
			'ai' => esc_html_x( 'Anguilla', 'country', 'sports-leagues' ),
			'aq' => esc_html_x( 'Antarctica', 'country', 'sports-leagues' ),
			'ag' => esc_html_x( 'Antigua and Barbuda', 'country', 'sports-leagues' ),
			'ar' => esc_html_x( 'Argentina', 'country', 'sports-leagues' ),
			'am' => esc_html_x( 'Armenia', 'country', 'sports-leagues' ),
			'aw' => esc_html_x( 'Aruba', 'country', 'sports-leagues' ),
			'au' => esc_html_x( 'Australia', 'country', 'sports-leagues' ),
			'at' => esc_html_x( 'Austria', 'country', 'sports-leagues' ),
			'az' => esc_html_x( 'Azerbaijan', 'country', 'sports-leagues' ),
			'bs' => esc_html_x( 'Bahamas', 'country', 'sports-leagues' ),
			'bh' => esc_html_x( 'Bahrain', 'country', 'sports-leagues' ),
			'bd' => esc_html_x( 'Bangladesh', 'country', 'sports-leagues' ),
			'bb' => esc_html_x( 'Barbados', 'country', 'sports-leagues' ),
			'by' => esc_html_x( 'Belarus', 'country', 'sports-leagues' ),
			'be' => esc_html_x( 'Belgium', 'country', 'sports-leagues' ),
			'bz' => esc_html_x( 'Belize', 'country', 'sports-leagues' ),
			'bj' => esc_html_x( 'Benin', 'country', 'sports-leagues' ),
			'bm' => esc_html_x( 'Bermuda', 'country', 'sports-leagues' ),
			'bt' => esc_html_x( 'Bhutan', 'country', 'sports-leagues' ),
			'bo' => esc_html_x( 'Bolivia', 'country', 'sports-leagues' ),
			'ba' => esc_html_x( 'Bosnia and Herzegovina', 'country', 'sports-leagues' ),
			'bw' => esc_html_x( 'Botswana', 'country', 'sports-leagues' ),
			'bv' => esc_html_x( 'Bouvet Island', 'country', 'sports-leagues' ),
			'br' => esc_html_x( 'Brazil', 'country', 'sports-leagues' ),
			'io' => esc_html_x( 'British Indian Ocean Territory', 'country', 'sports-leagues' ),
			'bn' => esc_html_x( 'Brunei Darussalam', 'country', 'sports-leagues' ),
			'bg' => esc_html_x( 'Bulgaria', 'country', 'sports-leagues' ),
			'bf' => esc_html_x( 'Burkina Faso', 'country', 'sports-leagues' ),
			'bi' => esc_html_x( 'Burundi', 'country', 'sports-leagues' ),
			'kh' => esc_html_x( 'Cambodia', 'country', 'sports-leagues' ),
			'cm' => esc_html_x( 'Cameroon', 'country', 'sports-leagues' ),
			'ca' => esc_html_x( 'Canada', 'country', 'sports-leagues' ),
			'cv' => esc_html_x( 'Cape Verde', 'country', 'sports-leagues' ),
			'ky' => esc_html_x( 'Cayman Islands', 'country', 'sports-leagues' ),
			'cf' => esc_html_x( 'Central African Republic', 'country', 'sports-leagues' ),
			'td' => esc_html_x( 'Chad', 'country', 'sports-leagues' ),
			'cl' => esc_html_x( 'Chile', 'country', 'sports-leagues' ),
			'cn' => esc_html_x( 'China', 'country', 'sports-leagues' ),
			'cx' => esc_html_x( 'Christmas Island', 'country', 'sports-leagues' ),
			'cc' => esc_html_x( 'Cocos (Keeling) Islands', 'country', 'sports-leagues' ),
			'co' => esc_html_x( 'Colombia', 'country', 'sports-leagues' ),
			'km' => esc_html_x( 'Comoros', 'country', 'sports-leagues' ),
			'cg' => esc_html_x( 'Republic of the Congo', 'country', 'sports-leagues' ),
			'cd' => esc_html_x( 'Democratic Republic of the Congo', 'country', 'sports-leagues' ),
			'ck' => esc_html_x( 'Cook Islands', 'country', 'sports-leagues' ),
			'cr' => esc_html_x( 'Costa Rica', 'country', 'sports-leagues' ),
			'hr' => esc_html_x( 'Croatia (Hrvatska)', 'country', 'sports-leagues' ),
			'cu' => esc_html_x( 'Cuba', 'country', 'sports-leagues' ),
			'cw' => esc_html_x( 'Curaçao', 'country', 'sports-leagues' ),
			'cy' => esc_html_x( 'Cyprus', 'country', 'sports-leagues' ),
			'cz' => esc_html_x( 'Czech Republic', 'country', 'sports-leagues' ),
			'dk' => esc_html_x( 'Denmark', 'country', 'sports-leagues' ),
			'dj' => esc_html_x( 'Djibouti', 'country', 'sports-leagues' ),
			'dm' => esc_html_x( 'Dominica', 'country', 'sports-leagues' ),
			'do' => esc_html_x( 'Dominican Republic', 'country', 'sports-leagues' ),
			'tp' => esc_html_x( 'East Timor', 'country', 'sports-leagues' ),
			'ec' => esc_html_x( 'Ecuador', 'country', 'sports-leagues' ),
			'eg' => esc_html_x( 'Egypt', 'country', 'sports-leagues' ),
			'sv' => esc_html_x( 'El Salvador', 'country', 'sports-leagues' ),
			'gq' => esc_html_x( 'Equatorial Guinea', 'country', 'sports-leagues' ),
			'er' => esc_html_x( 'Eritrea', 'country', 'sports-leagues' ),
			'ee' => esc_html_x( 'Estonia', 'country', 'sports-leagues' ),
			'et' => esc_html_x( 'Ethiopia', 'country', 'sports-leagues' ),
			'fk' => esc_html_x( 'Falkland Islands (Malvinas)', 'country', 'sports-leagues' ),
			'fo' => esc_html_x( 'Faroe Islands', 'country', 'sports-leagues' ),
			'fj' => esc_html_x( 'Fiji', 'country', 'sports-leagues' ),
			'fi' => esc_html_x( 'Finland', 'country', 'sports-leagues' ),
			'fr' => esc_html_x( 'France', 'country', 'sports-leagues' ),
			'fx' => esc_html_x( 'France, Metropolitan', 'country', 'sports-leagues' ),
			'gf' => esc_html_x( 'French Guiana', 'country', 'sports-leagues' ),
			'pf' => esc_html_x( 'French Polynesia', 'country', 'sports-leagues' ),
			'tf' => esc_html_x( 'French Southern Territories', 'country', 'sports-leagues' ),
			'ga' => esc_html_x( 'Gabon', 'country', 'sports-leagues' ),
			'gm' => esc_html_x( 'Gambia', 'country', 'sports-leagues' ),
			'ge' => esc_html_x( 'Georgia', 'country', 'sports-leagues' ),
			'de' => esc_html_x( 'Germany', 'country', 'sports-leagues' ),
			'gh' => esc_html_x( 'Ghana', 'country', 'sports-leagues' ),
			'gi' => esc_html_x( 'Gibraltar', 'country', 'sports-leagues' ),
			'gk' => esc_html_x( 'Guernsey', 'country', 'sports-leagues' ),
			'gr' => esc_html_x( 'Greece', 'country', 'sports-leagues' ),
			'gl' => esc_html_x( 'Greenland', 'country', 'sports-leagues' ),
			'gd' => esc_html_x( 'Grenada', 'country', 'sports-leagues' ),
			'gp' => esc_html_x( 'Guadeloupe', 'country', 'sports-leagues' ),
			'gu' => esc_html_x( 'Guam', 'country', 'sports-leagues' ),
			'gt' => esc_html_x( 'Guatemala', 'country', 'sports-leagues' ),
			'gn' => esc_html_x( 'Guinea', 'country', 'sports-leagues' ),
			'gw' => esc_html_x( 'Guinea-Bissau', 'country', 'sports-leagues' ),
			'gy' => esc_html_x( 'Guyana', 'country', 'sports-leagues' ),
			'ht' => esc_html_x( 'Haiti', 'country', 'sports-leagues' ),
			'hm' => esc_html_x( 'Heard and Mc Donald Islands', 'country', 'sports-leagues' ),
			'hn' => esc_html_x( 'Honduras', 'country', 'sports-leagues' ),
			'hk' => esc_html_x( 'Hong Kong', 'country', 'sports-leagues' ),
			'hu' => esc_html_x( 'Hungary', 'country', 'sports-leagues' ),
			'is' => esc_html_x( 'Iceland', 'country', 'sports-leagues' ),
			'in' => esc_html_x( 'India', 'country', 'sports-leagues' ),
			'im' => esc_html_x( 'Isle of Man', 'country', 'sports-leagues' ),
			'id' => esc_html_x( 'Indonesia', 'country', 'sports-leagues' ),
			'ir' => esc_html_x( 'Iran (Islamic Republic of)', 'country', 'sports-leagues' ),
			'iq' => esc_html_x( 'Iraq', 'country', 'sports-leagues' ),
			'ie' => esc_html_x( 'Ireland', 'country', 'sports-leagues' ),
			'il' => esc_html_x( 'Israel', 'country', 'sports-leagues' ),
			'it' => esc_html_x( 'Italy', 'country', 'sports-leagues' ),
			'ci' => esc_html_x( 'Ivory Coast', 'country', 'sports-leagues' ),
			'je' => esc_html_x( 'Jersey', 'country', 'sports-leagues' ),
			'jm' => esc_html_x( 'Jamaica', 'country', 'sports-leagues' ),
			'jp' => esc_html_x( 'Japan', 'country', 'sports-leagues' ),
			'jo' => esc_html_x( 'Jordan', 'country', 'sports-leagues' ),
			'kz' => esc_html_x( 'Kazakhstan', 'country', 'sports-leagues' ),
			'ke' => esc_html_x( 'Kenya', 'country', 'sports-leagues' ),
			'ki' => esc_html_x( 'Kiribati', 'country', 'sports-leagues' ),
			'kp' => esc_html_x( 'Korea, Democratic People\'s Republic of', 'country', 'sports-leagues' ),
			'kr' => esc_html_x( 'Korea, Republic of', 'country', 'sports-leagues' ),
			'xk' => esc_html_x( 'Kosovo', 'country', 'sports-leagues' ),
			'kw' => esc_html_x( 'Kuwait', 'country', 'sports-leagues' ),
			'kg' => esc_html_x( 'Kyrgyzstan', 'country', 'sports-leagues' ),
			'la' => esc_html_x( 'Lao People\'s Democratic Republic', 'country', 'sports-leagues' ),
			'lv' => esc_html_x( 'Latvia', 'country', 'sports-leagues' ),
			'lb' => esc_html_x( 'Lebanon', 'country', 'sports-leagues' ),
			'ls' => esc_html_x( 'Lesotho', 'country', 'sports-leagues' ),
			'lr' => esc_html_x( 'Liberia', 'country', 'sports-leagues' ),
			'ly' => esc_html_x( 'Libyan Arab Jamahiriya', 'country', 'sports-leagues' ),
			'li' => esc_html_x( 'Liechtenstein', 'country', 'sports-leagues' ),
			'lt' => esc_html_x( 'Lithuania', 'country', 'sports-leagues' ),
			'lu' => esc_html_x( 'Luxembourg', 'country', 'sports-leagues' ),
			'mo' => esc_html_x( 'Macau', 'country', 'sports-leagues' ),
			'mk' => esc_html_x( 'Macedonia', 'country', 'sports-leagues' ),
			'mg' => esc_html_x( 'Madagascar', 'country', 'sports-leagues' ),
			'mw' => esc_html_x( 'Malawi', 'country', 'sports-leagues' ),
			'my' => esc_html_x( 'Malaysia', 'country', 'sports-leagues' ),
			'mv' => esc_html_x( 'Maldives', 'country', 'sports-leagues' ),
			'ml' => esc_html_x( 'Mali', 'country', 'sports-leagues' ),
			'mt' => esc_html_x( 'Malta', 'country', 'sports-leagues' ),
			'mh' => esc_html_x( 'Marshall Islands', 'country', 'sports-leagues' ),
			'mq' => esc_html_x( 'Martinique', 'country', 'sports-leagues' ),
			'mr' => esc_html_x( 'Mauritania', 'country', 'sports-leagues' ),
			'mu' => esc_html_x( 'Mauritius', 'country', 'sports-leagues' ),
			'ty' => esc_html_x( 'Mayotte', 'country', 'sports-leagues' ),
			'mx' => esc_html_x( 'Mexico', 'country', 'sports-leagues' ),
			'fm' => esc_html_x( 'Micronesia, Federated States of', 'country', 'sports-leagues' ),
			'md' => esc_html_x( 'Moldova, Republic of', 'country', 'sports-leagues' ),
			'mc' => esc_html_x( 'Monaco', 'country', 'sports-leagues' ),
			'mn' => esc_html_x( 'Mongolia', 'country', 'sports-leagues' ),
			'me' => esc_html_x( 'Montenegro', 'country', 'sports-leagues' ),
			'ms' => esc_html_x( 'Montserrat', 'country', 'sports-leagues' ),
			'ma' => esc_html_x( 'Morocco', 'country', 'sports-leagues' ),
			'mz' => esc_html_x( 'Mozambique', 'country', 'sports-leagues' ),
			'mm' => esc_html_x( 'Myanmar', 'country', 'sports-leagues' ),
			'na' => esc_html_x( 'Namibia', 'country', 'sports-leagues' ),
			'nr' => esc_html_x( 'Nauru', 'country', 'sports-leagues' ),
			'np' => esc_html_x( 'Nepal', 'country', 'sports-leagues' ),
			'nl' => esc_html_x( 'Netherlands', 'country', 'sports-leagues' ),
			'an' => esc_html_x( 'Netherlands Antilles', 'country', 'sports-leagues' ),
			'nc' => esc_html_x( 'New Caledonia', 'country', 'sports-leagues' ),
			'nz' => esc_html_x( 'New Zealand', 'country', 'sports-leagues' ),
			'ni' => esc_html_x( 'Nicaragua', 'country', 'sports-leagues' ),
			'ne' => esc_html_x( 'Niger', 'country', 'sports-leagues' ),
			'ng' => esc_html_x( 'Nigeria', 'country', 'sports-leagues' ),
			'nu' => esc_html_x( 'Niue', 'country', 'sports-leagues' ),
			'nf' => esc_html_x( 'Norfolk Island', 'country', 'sports-leagues' ),
			'mp' => esc_html_x( 'Northern Mariana Islands', 'country', 'sports-leagues' ),
			'no' => esc_html_x( 'Norway', 'country', 'sports-leagues' ),
			'om' => esc_html_x( 'Oman', 'country', 'sports-leagues' ),
			'pk' => esc_html_x( 'Pakistan', 'country', 'sports-leagues' ),
			'pw' => esc_html_x( 'Palau', 'country', 'sports-leagues' ),
			'ps' => esc_html_x( 'Palestine', 'country', 'sports-leagues' ),
			'pa' => esc_html_x( 'Panama', 'country', 'sports-leagues' ),
			'pg' => esc_html_x( 'Papua New Guinea', 'country', 'sports-leagues' ),
			'py' => esc_html_x( 'Paraguay', 'country', 'sports-leagues' ),
			'pe' => esc_html_x( 'Peru', 'country', 'sports-leagues' ),
			'ph' => esc_html_x( 'Philippines', 'country', 'sports-leagues' ),
			'pn' => esc_html_x( 'Pitcairn', 'country', 'sports-leagues' ),
			'pl' => esc_html_x( 'Poland', 'country', 'sports-leagues' ),
			'pt' => esc_html_x( 'Portugal', 'country', 'sports-leagues' ),
			'pr' => esc_html_x( 'Puerto Rico', 'country', 'sports-leagues' ),
			'qa' => esc_html_x( 'Qatar', 'country', 'sports-leagues' ),
			're' => esc_html_x( 'Reunion', 'country', 'sports-leagues' ),
			'ro' => esc_html_x( 'Romania', 'country', 'sports-leagues' ),
			'ru' => esc_html_x( 'Russian Federation', 'country', 'sports-leagues' ),
			'rw' => esc_html_x( 'Rwanda', 'country', 'sports-leagues' ),
			'kn' => esc_html_x( 'Saint Kitts and Nevis', 'country', 'sports-leagues' ),
			'lc' => esc_html_x( 'Saint Lucia', 'country', 'sports-leagues' ),
			'vc' => esc_html_x( 'Saint Vincent and the Grenadines', 'country', 'sports-leagues' ),
			'ws' => esc_html_x( 'Samoa', 'country', 'sports-leagues' ),
			'sm' => esc_html_x( 'San Marino', 'country', 'sports-leagues' ),
			'st' => esc_html_x( 'Sao Tome and Principe', 'country', 'sports-leagues' ),
			'sa' => esc_html_x( 'Saudi Arabia', 'country', 'sports-leagues' ),
			'sn' => esc_html_x( 'Senegal', 'country', 'sports-leagues' ),
			'rs' => esc_html_x( 'Serbia', 'country', 'sports-leagues' ),
			'sc' => esc_html_x( 'Seychelles', 'country', 'sports-leagues' ),
			'sl' => esc_html_x( 'Sierra Leone', 'country', 'sports-leagues' ),
			'sg' => esc_html_x( 'Singapore', 'country', 'sports-leagues' ),
			'sk' => esc_html_x( 'Slovakia', 'country', 'sports-leagues' ),
			'si' => esc_html_x( 'Slovenia', 'country', 'sports-leagues' ),
			'sb' => esc_html_x( 'Solomon Islands', 'country', 'sports-leagues' ),
			'so' => esc_html_x( 'Somalia', 'country', 'sports-leagues' ),
			'za' => esc_html_x( 'South Africa', 'country', 'sports-leagues' ),
			'ss' => esc_html_x( 'South Sudan', 'country', 'sports-leagues' ),
			'gs' => esc_html_x( 'South Georgia South Sandwich Islands', 'country', 'sports-leagues' ),
			'es' => esc_html_x( 'Spain', 'country', 'sports-leagues' ),
			'lk' => esc_html_x( 'Sri Lanka', 'country', 'sports-leagues' ),
			'sh' => esc_html_x( 'St. Helena', 'country', 'sports-leagues' ),
			'pm' => esc_html_x( 'St. Pierre and Miquelon', 'country', 'sports-leagues' ),
			'sd' => esc_html_x( 'Sudan', 'country', 'sports-leagues' ),
			'sr' => esc_html_x( 'Suriname', 'country', 'sports-leagues' ),
			'sj' => esc_html_x( 'Svalbard and Jan Mayen Islands', 'country', 'sports-leagues' ),
			'sz' => esc_html_x( 'Swaziland', 'country', 'sports-leagues' ),
			'se' => esc_html_x( 'Sweden', 'country', 'sports-leagues' ),
			'ch' => esc_html_x( 'Switzerland', 'country', 'sports-leagues' ),
			'sy' => esc_html_x( 'Syrian Arab Republic', 'country', 'sports-leagues' ),
			'tw' => esc_html_x( 'Taiwan', 'country', 'sports-leagues' ),
			'tj' => esc_html_x( 'Tajikistan', 'country', 'sports-leagues' ),
			'tz' => esc_html_x( 'Tanzania, United Republic of', 'country', 'sports-leagues' ),
			'th' => esc_html_x( 'Thailand', 'country', 'sports-leagues' ),
			'tg' => esc_html_x( 'Togo', 'country', 'sports-leagues' ),
			'tk' => esc_html_x( 'Tokelau', 'country', 'sports-leagues' ),
			'to' => esc_html_x( 'Tonga', 'country', 'sports-leagues' ),
			'tt' => esc_html_x( 'Trinidad and Tobago', 'country', 'sports-leagues' ),
			'tn' => esc_html_x( 'Tunisia', 'country', 'sports-leagues' ),
			'tr' => esc_html_x( 'Turkey', 'country', 'sports-leagues' ),
			'tm' => esc_html_x( 'Turkmenistan', 'country', 'sports-leagues' ),
			'tc' => esc_html_x( 'Turks and Caicos Islands', 'country', 'sports-leagues' ),
			'tv' => esc_html_x( 'Tuvalu', 'country', 'sports-leagues' ),
			'ug' => esc_html_x( 'Uganda', 'country', 'sports-leagues' ),
			'ua' => esc_html_x( 'Ukraine', 'country', 'sports-leagues' ),
			'ae' => esc_html_x( 'United Arab Emirates', 'country', 'sports-leagues' ),
			'gb' => esc_html_x( 'United Kingdom', 'country', 'sports-leagues' ),
			'us' => esc_html_x( 'United States', 'country', 'sports-leagues' ),
			'um' => esc_html_x( 'United States minor outlying islands', 'country', 'sports-leagues' ),
			'uy' => esc_html_x( 'Uruguay', 'country', 'sports-leagues' ),
			'uz' => esc_html_x( 'Uzbekistan', 'country', 'sports-leagues' ),
			'vu' => esc_html_x( 'Vanuatu', 'country', 'sports-leagues' ),
			'va' => esc_html_x( 'Vatican City State', 'country', 'sports-leagues' ),
			've' => esc_html_x( 'Venezuela', 'country', 'sports-leagues' ),
			'vn' => esc_html_x( 'Vietnam', 'country', 'sports-leagues' ),
			'vg' => esc_html_x( 'Virgin Islands (British)', 'country', 'sports-leagues' ),
			'vi' => esc_html_x( 'Virgin Islands (U.S.)', 'country', 'sports-leagues' ),
			'wf' => esc_html_x( 'Wallis and Futuna Islands', 'country', 'sports-leagues' ),
			'eh' => esc_html_x( 'Western Sahara', 'country', 'sports-leagues' ),
			'ye' => esc_html_x( 'Yemen', 'country', 'sports-leagues' ),
			'zr' => esc_html_x( 'Zaire', 'country', 'sports-leagues' ),
			'zm' => esc_html_x( 'Zambia', 'country', 'sports-leagues' ),
			'zw' => esc_html_x( 'Zimbabwe', 'country', 'sports-leagues' ),
		];

		// Non standard Nations
		$this->countries = array_merge(
			$this->countries,
			[
				'_England'          => esc_html_x( 'England', 'country', 'sports-leagues' ),
				'_Northern_Ireland' => esc_html_x( 'Northern_Ireland', 'country', 'sports-leagues' ),
				'_Scotland'         => esc_html_x( 'Scotland', 'country', 'sports-leagues' ),
				'_Wales'            => esc_html_x( 'Wales', 'country', 'sports-leagues' ),
			]
		);

		/**
		 * Filter available countries.
		 *
		 * @param array  List of countries.
		 *
		 * @since 0.1.0
		 */
		$this->countries = apply_filters( 'sports-leagues/config/countries', $this->countries );
	}

	/**
	 * Get all countries.
	 *
	 * @return array
	 * @since 0.1.0
	 */
	public function get_countries() {

		$countries = $this->countries;
		asort( $countries );

		return $countries;
	}

	/**
	 * Getter for array of admin localization strings.
	 *
	 * @return array
	 * @since 0.1.0
	 */
	public function get_l10n_admin() {
		return [
			'add_color'                      => esc_html__( 'add color', 'sports-leagues' ),
			'add_initial_points'             => esc_html__( 'Add initial points', 'sports-leagues' ),
			'add_new_group_pair'             => esc_html__( 'Add New Group/Pair', 'sports-leagues' ),
			'add_new_stage'                  => esc_html__( 'Add New Stage', 'sports-leagues' ),
			'add_new_round'                  => esc_html__( 'Add New Round', 'sports-leagues' ),
			'add_player_or_staff_to_roster'  => esc_html__( 'Add Player or Staff to Season Roster', 'sports-leagues' ),
			'add_player'                     => esc_html__( 'Add Player', 'sports-leagues' ),
			'add_official'                   => esc_html__( 'Add Official', 'sports-leagues' ),
			'add_points'                     => esc_html__( 'add points', 'sports-leagues' ),
			'add_table_color'                => esc_html__( 'Add table color', 'sports-leagues' ),
			'agg_text'                       => esc_html__( 'Aggregate Text', 'sports-leagues' ),
			'aggtext_hint'                   => esc_html__( 'For Example: "Agg: 2-2; Team A won on penalties (5-3)"', 'sports-leagues' ),
			'are_you_sure'                   => esc_html__( 'Are you sure?', 'sports-leagues' ),
			'attach_teams_current_table'     => esc_html__( 'Attach teams to the current table', 'sports-leagues' ),
			'attendance'                     => esc_html__( 'Attendance', 'sports-leagues' ),
			'automatic_position_calculation' => esc_html__( 'Automatic position calculation', 'sports-leagues' ),
			'available_criteria'             => esc_html__( 'Available criteria', 'sports-leagues' ),
			'bonus_points_for_match'         => esc_html__( 'bonus points for match', 'sports-leagues' ),
			'change_roster_groups_tip'       => esc_html__( 'Change roster groups, player positions and status in Sport Configurator', 'sports-leagues' ),
			'close'                          => esc_html__( 'Close', 'sports-leagues' ),
			'color_by'                       => esc_html__( 'Color By', 'sports-leagues' ),
			'color'                          => esc_html__( 'Color', 'sports-leagues' ),
			'confirm_delete'                 => esc_html__( 'Confirm Delete', 'sports-leagues' ),
			'current_ranking_criteria'       => esc_html__( 'Current Ranking Criteria', 'sports-leagues' ),
			'danger'                         => esc_html__( 'danger', 'sports-leagues' ),
			'delete_round'                   => esc_html__( 'Delete Round', 'sports-leagues' ),
			'display_options'                => esc_html__( 'Display Options', 'sports-leagues' ),
			'draw'                           => esc_html__( 'Draw', 'sports-leagues' ),
			'edit'                           => esc_html__( 'edit', 'sports-leagues' ),
			'edit_stage'                     => esc_html__( 'edit stage', 'sports-leagues' ),
			'edit_player_number'             => esc_html__( 'edit player number', 'sports-leagues' ),
			'fill_final_scores_outcomes'     => esc_html__( 'Please fill final scores and outcomes for finished game!', 'sports-leagues' ),
			'final_score'                    => esc_html__( 'Final Score', 'sports-leagues' ),
			'finished'                       => esc_html__( 'finished', 'sports-leagues' ),
			'friendly'                       => esc_html__( 'friendly', 'sports-leagues' ),
			'from_previous_round'            => esc_html__( 'from previous round', 'sports-leagues' ),
			'full_time_loss'                 => esc_html__( 'Full-time loss', 'sports-leagues' ),
			'full_time_win'                  => esc_html__( 'Full-time win', 'sports-leagues' ),
			'game_day'                       => esc_html__( 'Game Day', 'sports-leagues' ),
			'games'                          => esc_html__( 'Games', 'sports-leagues' ),
			'game_kickoff'                   => esc_html__( 'Game Kickoff', 'sports-leagues' ),
			'game_number'                    => esc_html__( 'Game Number', 'sports-leagues' ),
			'game_setup'                     => esc_html__( 'Game Setup', 'sports-leagues' ),
			'game_team_stats'                => esc_html__( 'Game Team Stats', 'sports-leagues' ),
			'general_info'                   => esc_html__( 'General Info', 'sports-leagues' ),
			'group'                          => esc_html__( 'Group', 'sports-leagues' ),
			'ignore_group_structure'         => esc_html__( 'ignore group structure', 'sports-leagues' ),
			'info'                           => esc_html__( 'info', 'sports-leagues' ),
			'knockout'                       => esc_html__( 'Knockout', 'sports-leagues' ),
			'layout_mini_widget'             => esc_html__( 'Layout Mini (Widget)', 'sports-leagues' ),
			'league'                         => esc_html__( 'League', 'sports-leagues' ),
			'legend'                         => esc_html__( 'Legend', 'sports-leagues' ),
			'no'                             => esc_html__( 'No', 'sports-leagues' ),
			'not_available_for_status'       => esc_html__( 'Not available for status', 'sports-leagues' ),
			'notes_below_table'              => esc_html__( 'Notes Below Table', 'sports-leagues' ),
			'number'                         => esc_html__( 'Number', 'sports-leagues' ),
			'official'                       => esc_html__( 'official', 'sports-leagues' ),
			'official_not_exists'            => esc_html__( 'official not exists', 'sports-leagues' ),
			'officials'                      => esc_html__( 'Officials', 'sports-leagues' ),
			'oops_no_elements_found'         => esc_html__( 'Oops! No elements found. Consider changing the search query.', 'sports-leagues' ),
			'order'                          => esc_html__( 'Order', 'sports-leagues' ),
			'outcome'                        => esc_html__( 'Outcome', 'sports-leagues' ),
			'overtime'                       => esc_html__( 'overtime', 'sports-leagues' ),
			'overtime_loss'                  => esc_html__( 'Overtime loss', 'sports-leagues' ),
			'overtime_win'                   => esc_html__( 'Overtime win', 'sports-leagues' ),
			'pair'                           => esc_html__( 'Pair', 'sports-leagues' ),
			'penalty_loss'                   => esc_html__( 'Penalty loss', 'sports-leagues' ),
			'penalty_shootout'               => esc_html__( 'penalty shootout', 'sports-leagues' ),
			'penalty_win'                    => esc_html__( 'Penalty win', 'sports-leagues' ),
			'period'                         => esc_html__( 'period', 'sports-leagues' ),
			'person_name'                    => esc_html__( 'Person Name', 'sports-leagues' ),
			'place'                          => esc_html__( 'place', 'sports-leagues' ),
			'played'                         => esc_html__( 'Played', 'sports-leagues' ),
			'players_list'                   => esc_html__( 'Players List', 'sports-leagues' ),
			'points_and_outcome'             => esc_html__( 'Points and Outcome', 'sports-leagues' ),
			'points_awarded'                 => esc_html__( 'Standing Points', 'sports-leagues' ),
			'points_awarded_hint'            => esc_html__( 'Change number of given points in the "Sport Configurator" in plugin settings', 'sports-leagues' ),
			'points_for_match'               => esc_html__( 'points for match', 'sports-leagues' ),
			'points_initial'                 => esc_html__( 'Points Initial', 'sports-leagues' ),
			'position'                       => esc_html__( 'Position', 'sports-leagues' ),
			'primary'                        => esc_html__( 'primary', 'sports-leagues' ),
			'quickly_create_players_tip'     => esc_html__( 'Quickly create players with "Import Data" Tool', 'sports-leagues' ),
			'ranking_criteria'               => esc_html__( 'Ranking Criteria', 'sports-leagues' ),
			'ranking_criteria_hint_1'        => esc_html__( 'Ranking criteria are used to determine the position of the team in the Standing Table (from top to bottom).', 'sports-leagues' ),
			'ranking_criteria_hint_2'        => esc_html__( 'Only works if Automatic Position Calculation is set to "YES"', 'sports-leagues' ),
			'recommended_title_tournament'   => esc_html__( 'Recommended title is "LEAGUE - SEASON" (My League - 2019)', 'sports-leagues' ),
			'remove_group'                   => esc_html__( 'Remove group', 'sports-leagues' ),
			'remove_only_empty_round'        => esc_html__( 'You can remove only empty Round! Please delete all attached groups/pairs first.', 'sports-leagues' ),
			'round'                          => esc_html__( 'Round', 'sports-leagues' ),
			'round_title'                    => esc_html__( 'Round Title', 'sports-leagues' ),
			'save'                           => esc_html__( 'Save', 'sports-leagues' ),
			'save_and_continue'              => esc_html__( 'Save and Continue', 'sports-leagues' ),
			'save_changes'                   => esc_html__( 'Save Changes', 'sports-leagues' ),
			'season'                         => esc_html__( 'Season', 'sports-leagues' ),
			'secondary'                      => esc_html__( 'secondary', 'sports-leagues' ),
			'select'                         => esc_html__( 'select', 'sports-leagues' ),
			'select_team'                    => esc_html__( 'Select Team', 'sports-leagues' ),
			'select_two_teams_only'          => esc_html__( 'Select max two teams', 'sports-leagues' ),
			'select_venue'                   => esc_html__( 'select venue', 'sports-leagues' ),
			'set_outcome'                    => esc_html__( 'set outcome', 'sports-leagues' ),
			'show_all_teams_ignoring'        => esc_html__( 'Show all teams ignoring group structure (not recommended)', 'sports-leagues' ),
			'stage'                          => esc_html__( 'Stage', 'sports-leagues' ),
			'stage_order_desc'               => esc_html__( 'Set stage order by entering a number (1 for first, etc.)', 'sports-leagues' ),
			'stage_status'                   => esc_html__( 'Stage Status', 'sports-leagues' ),
			'stage_title'                    => esc_html__( 'Stage Title', 'sports-leagues' ),
			'standing_table'                 => esc_html__( 'Standing Table', 'sports-leagues' ),
			'standing_table_colors'          => esc_html__( 'Standing Table Colors', 'sports-leagues' ),
			'status'                         => esc_html__( 'Status', 'sports-leagues' ),
			'structure'                      => esc_html__( 'Structure', 'sports-leagues' ),
			'success'                        => esc_html__( 'success', 'sports-leagues' ),
			'system'                         => esc_html__( 'System', 'sports-leagues' ),
			'table_columns_visibility'       => esc_html__( 'Table columns (order and visibility)', 'sports-leagues' ),
			'team'                           => esc_html__( 'Team', 'sports-leagues' ),
			'team_away'                      => esc_html__( 'Team Away', 'sports-leagues' ),
			'team_home'                      => esc_html__( 'Team Home', 'sports-leagues' ),
			'team_roster'                    => esc_html__( 'Team Roster', 'sports-leagues' ),
			'team_initial_points'            => esc_html__( 'Team Initial Points', 'sports-leagues' ),
			'title'                          => esc_html__( 'Title', 'sports-leagues' ),
			'tournament_settings'            => esc_html__( 'Tournament Settings', 'sports-leagues' ),
			'tournament_stage'               => esc_html__( 'Tournament Stage', 'sports-leagues' ),
			'tournament_stages'              => esc_html__( 'Tournament Stages', 'sports-leagues' ),
			'tournament_system'              => esc_html__( 'Tournament System', 'sports-leagues' ),
			'tournament_title'               => esc_html__( 'Tournament Title', 'sports-leagues' ),
			'upcoming'                       => esc_html__( 'upcoming', 'sports-leagues' ),
			'use_batch_import_tool'          => esc_html__( 'Use Batch import tool for fast Teams creation', 'sports-leagues' ),
			'venue'                          => esc_html__( 'Venue', 'sports-leagues' ),
			'want_to_delete_round'           => esc_html__( 'Do you really want to delete Round?', 'sports-leagues' ),
			'warning'                        => esc_html__( 'warning', 'sports-leagues' ),
			'yes'                            => esc_html__( 'Yes', 'sports-leagues' ),
		];
	}

	/**
	 * Get country by code.
	 *
	 * @param string $code
	 *
	 * @return string
	 * @since 0.1.0
	 */
	public function get_country_by_code( $code ) {
		return empty( $this->countries[ $code ] ) ? '' : $this->countries[ $code ];
	}

	/**
	 * Get import options data.
	 *
	 * @return array
	 * @since 0.5.2
	 */
	public function get_import_options() {
		$options = [];

		// Positions
		$options['positions'] = Sports_Leagues_Config::get_value( 'position' ) ? : [];

		// Countries
		$options['countries'] = [];
		foreach ( $this->get_countries() as $country_code => $country_title ) {
			$options['countries'][] = [
				'id'   => $country_code,
				'name' => $country_title,
			];
		}

		// Teams
		$options['teams'] = [];
		foreach ( $this->plugin->team->get_team_options() as $team_id => $team_name ) {
			$options['teams'][] = [
				'id'   => $team_id,
				'name' => $team_name,
			];
		}

		return $options;
	}

	/**
	 * Array of localization strings for Vue Datepicker.
	 *
	 * @return array
	 * @since 0.10.0
	 */
	public function get_vue_datepicker_locale() {

		global $wp_locale;

		return [
			'formatLocale' => [
				'firstDayOfWeek' => absint( get_option( 'start_of_week' ) ),
				'months'         => array_values( $wp_locale->month ),
				'monthsShort'    => array_values( $wp_locale->month_abbrev ),
				'weekdays'       => array_values( $wp_locale->weekday ),
				'weekdaysShort'  => array_values( $wp_locale->weekday_abbrev ),
				'weekdaysMin'    => array_values( $wp_locale->weekday_abbrev ),
			],
		];
	}

	/**
	 * Array of localization strings for Dashboard.
	 *
	 * @return array
	 * @since 0.11.0
	 */
	public function get_dashboard_data() {

		/*
		|--------------------------------------------------------------------
		| Prepare Sport Config
		|--------------------------------------------------------------------
		*/
		$all_config_data = Sports_Leagues_Config::get_value( 'all' );
		$prepared_config = [];

		// Player Positions
		if ( ! empty( $all_config_data['position'] ) ) {

			$positions = [];

			foreach ( $all_config_data['position'] as $position ) {
				if ( $position['name'] ) {
					$positions[] = $position['name'];
				}
			}

			if ( ! empty( $positions ) ) {
				$prepared_config[] = [
					'label' => 'Player Position',
					'data'  => implode( ', ', $positions ),
				];
			}
		}

		// Player Roster Status
		if ( ! empty( $all_config_data['roster_status'] ) ) {

			$prepared_config[] = [
				'label' => 'Player Roster Status',
				'data'  => implode( ', ', $all_config_data['roster_status'] ),
			];
		}

		// Player Roster Groups
		if ( ! empty( $all_config_data['roster_groups'] ) ) {

			$groups = [];

			foreach ( $all_config_data['roster_groups'] as $group ) {
				if ( $group['name'] ) {
					$groups[] = $group['name'];
				}
			}

			if ( ! empty( $groups ) ) {
				$prepared_config[] = [
					'label' => 'Player Roster Groups',
					'data'  => implode( ', ', $groups ),
				];
			}
		}

		// Player Game Groups
		if ( ! empty( $all_config_data['game_player_groups'] ) ) {

			$groups = [];

			foreach ( $all_config_data['game_player_groups'] as $group ) {
				if ( $group['name'] ) {
					$groups[] = $group['name'];
				}
			}

			if ( ! empty( $groups ) ) {
				$prepared_config[] = [
					'label' => 'Player Game Groups',
					'data'  => implode( ', ', $groups ),
				];
			}
		}

		/*
		|--------------------------------------------------------------------
		| Prepare Game Events
		|--------------------------------------------------------------------
		*/
		$all_events = Sports_Leagues_Event::get_value( 'all' );
		$events     = [];

		if ( ! empty( $all_events['events'] ) ) {
			foreach ( $all_events['events'] as $event ) {
				$events[] = $event['name'];
			}
		}

		/*
		|--------------------------------------------------------------------
		| Prepare Game Events
		|--------------------------------------------------------------------
		*/
		$player_stats = get_option( 'sl_columns_game', [] );
		$stats        = [];

		if ( ! empty( $player_stats ) ) {

			$player_stats = json_decode( $player_stats );

			foreach ( $player_stats as $stat_value ) {
				if ( ! empty( $stat_value->name ) ) {
					$stats[] = $stat_value->name;
				}
			}
		}

		return [
			'dashboardData'    => [
				'sport' => Sports_Leagues_Config::get_value( 'sport' ),
			],
			'addNewLeague'     => admin_url( 'edit-tags.php?taxonomy=sl_league&post_type=sl_tournament' ),
			'addNewSeason'     => admin_url( 'edit-tags.php?taxonomy=sl_season&post_type=sl_tournament' ),
			'addNewTournament' => admin_url( 'post-new.php?post_type=sl_tournament' ),
			'addNewTeam'       => admin_url( 'post-new.php?post_type=sl_team' ),
			'addNewPlayer'     => admin_url( 'post-new.php?post_type=sl_player' ),
			'addNewGame'       => admin_url( 'post-new.php?post_type=sl_game' ),
			'addNewStanding'   => admin_url( 'post-new.php?post_type=sl_standing' ),
			'addNewOfficial'   => admin_url( 'post-new.php?post_type=sl_official' ),
			'addNewStaff'      => admin_url( 'post-new.php?post_type=sl_staff' ),
			'addNewVenue'      => admin_url( 'post-new.php?post_type=sl_venue' ),
			'allTournaments'   => admin_url( 'edit.php?post_type=sl_tournament' ),
			'allTeams'         => admin_url( 'edit.php?post_type=sl_team' ),
			'allPlayers'       => admin_url( 'edit.php?post_type=sl_player' ),
			'allGames'         => admin_url( 'edit.php?post_type=sl_game' ),
			'allStandings'     => admin_url( 'edit.php?post_type=sl_standing' ),
			'allOfficials'     => admin_url( 'edit.php?post_type=sl_official' ),
			'allStaff'         => admin_url( 'edit.php?post_type=sl_staff' ),
			'allVenues'        => admin_url( 'edit.php?post_type=sl_venue' ),
			'importTeams'      => admin_url( 'admin.php?page=sl-import-tool&tool=teams' ),
			'importPlayers'    => admin_url( 'admin.php?page=sl-import-tool' ),
			'importOfficials'  => admin_url( 'admin.php?page=sl-import-tool&tool=officials' ),
			'importStaff'      => admin_url( 'admin.php?page=sl-import-tool&tool=staff' ),
			'importVenues'     => admin_url( 'admin.php?page=sl-import-tool&tool=venues' ),
			'openGameEvents'   => admin_url( 'admin.php?page=sports_leagues_event' ),
			'openPlayerStats'  => admin_url( 'admin.php?page=sl-player-stats' ),
			'openSportConfig'  => admin_url( 'admin.php?page=sl-configurator' ),
			'options'          => [
				'availableSports' => [
					[
						'label' => '🏀 ' . esc_html__( 'Basketball', 'sports-leagues' ),
						'value' => 'basketball',
					],
					[
						'label' => '🤾 ' . esc_html__( 'Handball', 'sports-leagues' ),
						'value' => 'handball',
					],
					[
						'label' => '🏉 ' . esc_html__( 'Rugby', 'sports-leagues' ),
						'value' => 'rugby',
					],
					[
						'label' => '🏒 ' . esc_html__( 'Ice Hockey', 'sports-leagues' ),
						'value' => 'ice_hockey',
					],
					[
						'label' => '🏈 ' . esc_html__( 'American football', 'sports-leagues' ),
						'value' => 'football',
					],
					[
						'label' => esc_html__( 'Other', 'sports-leagues' ),
						'value' => 'other',
					],
				],
				'leagues'         => array_values( sports_leagues()->league->get_league_options() ),
				'seasons'         => array_values( sports_leagues()->season->get_season_options() ),
				'teams'           => array_values( sports_leagues()->team->get_team_options() ),
				'tournaments'     => array_values( sports_leagues()->tournament->get_root_tournament_options() ),
				'configurator'    => $prepared_config,
				'events'          => $events,
				'playerStats'     => $stats,
				'playersNumber'   => get_posts(
					[
						'numberposts' => - 1,
						'post_type'   => 'sl_player',
						'fields'      => 'ids',
					]
				),
				'gamesNumber'     => get_posts(
					[
						'numberposts' => - 1,
						'post_type'   => 'sl_game',
						'fields'      => 'ids',
					]
				),
				'standingsNumber' => get_posts(
					[
						'numberposts' => - 1,
						'post_type'   => 'sl_standing',
						'fields'      => 'ids',
					]
				),
				'officialsNumber' => get_posts(
					[
						'numberposts' => - 1,
						'post_type'   => 'sl_official',
						'fields'      => 'ids',
					]
				),
				'staffNumber'     => get_posts(
					[
						'numberposts' => - 1,
						'post_type'   => 'sl_staff',
						'fields'      => 'ids',
					]
				),
				'venuesNumber'    => get_posts(
					[
						'numberposts' => - 1,
						'post_type'   => 'sl_venue',
						'fields'      => 'ids',
					]
				),
			],
		];
	}
	/**
	 * Array of localization strings for Dashboard.
	 *
	 * @return array
	 * @since 0.11.0
	 */
	public function get_events_data() {

		$events_data = [
			'options'  => sports_leagues()->event->get_configurator_events(),
			'icons'    => sports_leagues()->event->get_event_icons(),
			'tooltips' => [
				'game_header' => [
					'img' => esc_url( Sports_Leagues::url( 'admin/img/tutorial/event-icon-game-header.png' ) ),
				],
				'player_list' => [
					'img' => esc_url( Sports_Leagues::url( 'admin/img/tutorial/event-icon-player-list.png' ) ),
				],
			],
		];

		return apply_filters( 'sports-leagues/events-config/events-data', $events_data );
	}
}
