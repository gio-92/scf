<?php

/**
 * Plugin Name:       SCF - Simple Contanct Form
 * Description:       Plugin per creare un semplice modulo dei contatti in maniera dinamica in lingua italiana.
 * Version:           1.0.0
 * Requires at least: 5.2
 * Requires PHP:      7.2
 * Author:            Giorgio Gabatel
 * License:           GPL v2 or later
 * License URI:       https://www.gnu.org/licenses/gpl-2.0.html
 * Domain Path:       /languages
 */

function scf_scripts(){
	wp_enqueue_script('scf-script', plugin_dir_url(__FILE__) . 'js/scf.js', array(), '1.0.0', true);
	wp_enqueue_style('scf-style', plugin_dir_url(__FILE__) . 'css/scf.css', NULL, microtime());
}
add_action('wp_enqueue_scripts', 'scf_scripts');

function scf_admin_scripts(){
	wp_enqueue_script('scf-admin-script', plugin_dir_url(__FILE__) . 'js/scf-admin.js', array(), '1.0.0', true);
	wp_enqueue_style('scf-admin-styles', plugin_dir_url(__FILE__) . 'css/scf-admin.css', NULL, microtime());
}
add_action('admin_enqueue_scripts', 'scf_admin_scripts');

function scf_register_settings(){
	add_option('field_submit', 'Invia');	
	add_option('field_submit_color', '#00ff00');	
	add_option('field_name_label', 'Nome');	
	add_option('field_name_placeholder', 'Inserisci il tuo nome');
	add_option('field_email_label', 'Email');
	add_option('field_email_placeholder', 'Inserisci la tua email');
	add_option('field_subject_label', 'Oggetto');
	add_option('field_subject_placeholder', 'Inserisci l\'oggetto della mail');
	add_option('field_message_label', 'Messaggio');
	add_option('field_message_placeholder', 'Inserisci il messaggio');

	register_setting('scf_options_group', 'field_submit');
	register_setting('scf_options_group', 'field_submit_color');
	register_setting('scf_options_group', 'field_name_label');
	register_setting('scf_options_group', 'field_name_placeholder');
	register_setting('scf_options_group', 'field_email_label');
	register_setting('scf_options_group', 'field_email_placeholder');
	register_setting('scf_options_group', 'field_subject_label');
	register_setting('scf_options_group', 'field_subject_placeholder');
	register_setting('scf_options_group', 'field_message_label');
	register_setting('scf_options_group', 'field_message_placeholder');
}
add_action('admin_init', 'scf_register_settings');

class Scf {
	private $scf_options;

	public function __construct() {
		add_action( 'admin_menu', array( $this, 'scf_add_plugin_page' ) );
		add_action( 'admin_init', array( $this, 'scf_page_init' ) );
	}

	public function scf_add_plugin_page() {
		add_menu_page(
			'SCF - Simple Contanct Form', // titolo pagina
			'SCF - Simple Contanct Form', // titolo menù
			'manage_options', // capability
			'scf', // slug
			array( $this, 'scf_create_admin_page' ), // function
			'dashicons-schedule', // icon
			75 // position
		);
        add_submenu_page(
            'scf', // slug del menù principale
            'SCF - Form', // titolo pagina
            'SCF - Form', // titolo menù
            'manage_options', // capability 
            'scf_sub', // slug sub menù
            array($this, 'scf_function_submenu') // function
        );
	}

	public function scf_create_admin_page() {
		$this->scf_options = get_option( 'scf_option_name' ); ?>

		<div class="wrap">
			<h2>SCF - Simple Contanct Form</h2>
            <div class="scf-success">Inserisci lo shortcode <strong>[scf]</strong> ovunque nel sito per attivare il modulo di contatto.</div>
			<h3>Compila i seguenti campi per configurare correttamente il plugin.</h3>
			<?php settings_errors(); ?>

			<form method="post" action="options.php">
				<?php
					settings_fields( 'scf_option_group' );
					do_settings_sections( 'scf-admin' );
					submit_button();
				?>
			</form>
		</div>
	<?php }

	public function scf_page_init() {
		register_setting(
			'scf_option_group', // option_group
			'scf_option_name', // option_name
			array( $this, 'scf_sanitize' ) // sanitize_callback
		);

		add_settings_section(
			'scf_setting_section', // id
			'', // title
			array( $this, 'scf_section_info' ), // callback
			'scf-admin' // page
		);

		add_settings_field(
			'email_0', // id
			'Email', // title
			array( $this, 'email_0_callback' ), // callback
			'scf-admin', // page
			'scf_setting_section' // section
		);

		add_settings_field(
			'disattiva_1', // id
			'Input nome', // title
			array( $this, 'disattiva_1_callback' ), // callback
			'scf-admin', // page
			'scf_setting_section' // section
		);

		add_settings_field(
			'disattiva_2', // id
			'Input email', // title
			array( $this, 'disattiva_2_callback' ), // callback
			'scf-admin', // page
			'scf_setting_section' // section
		);

		add_settings_field(
			'disattiva_3', // id
			'Input oggetto', // title
			array( $this, 'disattiva_3_callback' ), // callback
			'scf-admin', // page
			'scf_setting_section' // section
		);

		add_settings_field(
			'disattiva_4', // id
			'Input messaggio', // title
			array( $this, 'disattiva_4_callback' ), // callback
			'scf-admin', // page
			'scf_setting_section' // section
		);

		add_settings_field(
			'subject_5', // id
			'Oggetto personalizzato', // title
			array( $this, 'subject_5_callback' ), // callback
			'scf-admin', // page
			'scf_setting_section' // section
		);
	}

	public function scf_sanitize($input) {
		$sanitary_values = array();
		if ( isset( $input['email_0'] ) ) {
			$sanitary_values['email_0'] = sanitize_text_field( $input['email_0'] );
		}

		if ( isset( $input['disattiva_1'] ) ) {
			$sanitary_values['disattiva_1'] = $input['disattiva_1'];
		}

		if ( isset( $input['disattiva_2'] ) ) {
			$sanitary_values['disattiva_2'] = $input['disattiva_2'];
		}

		if ( isset( $input['disattiva_3'] ) ) {
			$sanitary_values['disattiva_3'] = $input['disattiva_3'];
		}

		if ( isset( $input['disattiva_4'] ) ) {
			$sanitary_values['disattiva_4'] = $input['disattiva_4'];
		}

		if ( isset( $input['subject_5'] ) ) {
			$sanitary_values['subject_5'] = sanitize_text_field( $input['subject_5'] );
		}

		return $sanitary_values;
	}

	public function scf_section_info() {
		
	}

	public function email_0_callback() {
		printf(
			'<input class="regular-text" type="text" name="scf_option_name[email_0]" id="email_0" placeholder="Inserisci la mail dell\'amministratore" value="%s" required>
            <br><small><i><strong>Importante: </strong>inserisci la email dove desideri ricevere le comunicazioni. 
            <div class="scf-must">* Campo Obbligatorio</div></i></small>',
			isset( $this->scf_options['email_0'] ) ? esc_attr( $this->scf_options['email_0']) : ''
		);
	}

	public function disattiva_1_callback() {
		printf(
			'<input type="checkbox" name="scf_option_name[disattiva_1]" id="disattiva_1" value="disattiva_1" %s> <label for="disattiva_1"> Disattiva </label>',
			( isset( $this->scf_options['disattiva_1'] ) && $this->scf_options['disattiva_1'] === 'disattiva_1' ) ? 'checked' : ''
		);
	}

	public function disattiva_2_callback() {
		printf(
			'<input type="checkbox" name="scf_option_name[disattiva_2]" id="disattiva_2" value="disattiva_2" %s> <label for="disattiva_2"> Disattiva </label>',
			( isset( $this->scf_options['disattiva_2'] ) && $this->scf_options['disattiva_2'] === 'disattiva_2' ) ? 'checked' : ''
		);
	}

	public function disattiva_3_callback() {
		printf(
			'<input type="checkbox" name="scf_option_name[disattiva_3]" id="disattiva_3" value="disattiva_3" %s> <label for="disattiva_3"> Disattiva </label>',
			( isset( $this->scf_options['disattiva_3'] ) && $this->scf_options['disattiva_3'] === 'disattiva_3' ) ? 'checked' : ''
		);
	}

	public function disattiva_4_callback() {
		printf(
			'<input type="checkbox" name="scf_option_name[disattiva_4]" id="disattiva_4" value="disattiva_4" %s> <label for="disattiva_4"> Disattiva </label>',
			( isset( $this->scf_options['disattiva_4'] ) && $this->scf_options['disattiva_4'] === 'disattiva_4' ) ? 'checked' : ''
		);
	}

	public function subject_5_callback() {
		printf(
			'<input class="regular-text" type="text" name="scf_option_name[subject_5]" id="subject_5" placeholder="Inserisci l\'oggetto della mail" value="%s" required>
			<br><small><i><strong>Importante: </strong>la mail personalizzata verrà impostata solo in caso l\'input oggetto sia disattivato. 
			<div class="scf-must">* Campo Obbligatorio</div></i></small>',
			isset( $this->scf_options['subject_5'] ) ? esc_attr( $this->scf_options['subject_5']) : ''
		);
	}

    public function scf_function_submenu(){?>
        <h2>Personalizza la form.</h2>
        <form method="post" action="options.php">
            <?php settings_fields('scf_options_group'); ?>
            <div class="scf-container">
                <label for="field_submit"><strong>Testo del bottone</strong></label>
                <input type="text" class="regular-text" id="field_submit" name="field_submit" placeholder="Inserisci il testo del bottone" value="<?php echo get_option('field_submit') ?>" />                
                <br><br>
                <label for="field_submit_color"><strong>Colore del bottone</strong></label>
                <input type="color" id="field_submit_color" name="field_submit_color" value="<?php echo get_option('field_submit_color') ?>">
            </div>

            <br><hr><br>

            <div class="scf-container">			
                <label for="field_name_label">Inserisci la label relativa al nome</label>
                <input type="text" class="regular-text" id="field_name_label" name="field_name_label" placeholder="Inserisci qui la label relativa al nome" value="<?php echo get_option('field_name_label') ?>" />
                <br><br>
                <label for="field_name_placeholder">Inserisci il placeholder relativo al nome</label>
                <input type="text" class="regular-text" id="field_name_placeholder" name="field_name_placeholder" placeholder="Inserisci qui il placeholder relativo al nome" value="<?php echo get_option('field_name_placeholder') ?>" />
            </div>

            <br><hr><br>

            <div class="scf-container">
                <label for="field_email_label">Inserisci la label relativa alla email</label>
                <input type="text" class="regular-text" id="field_email_label" name="field_email_label" placeholder="Inserisci qui la label relativa alla email" value="<?php echo get_option('field_email_label') ?>" />
                <br><br>
                <label for="field_email_placeholder">Inserisci il placeholder relativo alla mail</label>
                <input type="text" class="regular-text" id="field_email_placeholder" name="field_email_placeholder" placeholder="Inserisci qui il placeholder relativo alla email" value="<?php echo get_option('field_email_placeholder') ?>" />
                <br><br>
            </div>

            <br><hr><br>

            <div class="scf-container">
                <label for="field_subject_label">Inserisci la label dell'oggetto</label>
                <input type="text" class="regular-text" id="field_subject_label" name="field_subject_label" placeholder="Inserisci qui la label relativa all'oggetto" value="<?php echo get_option('field_subject_label') ?>" />
                <br><br>
                <label for="field_subject_placeholder">Inserisci il placeholder relativo all'oggetto</label>
                <input type="text" class="regular-text" id="field_subject_placeholder" name="field_subject_placeholder" placeholder="Inserisci qui il placeholder relativo all'oggetto" value="<?php echo get_option('field_subject_placeholder') ?>" />
            </div>

            <br><hr><br>

            <div class="scf-container">
                <label for="field_message_label">Inserisci la label del messaggio</label>
                <input type="text" class="regular-text" id="field_message_label" name="field_message_label" placeholder="Inserisci qui la label relativa all'oggetto" value="<?php echo get_option('field_message_label') ?>" />
                <br><br>
                <label for="field_message_placeholder">Inserisci il placeholder relativo al messaggio</label>
                <input type="text" class="regular-text" id="field_message_placeholder" name="field_message_placeholder" placeholder="Inserisci qui il placeholder relativo all'oggetto" value="<?php echo get_option('field_message_placeholder') ?>" />
            </div>

            <?php submit_button(); ?>
        </form><?php
    } 

}

function scf_form(){
	echo  '
		<form class="scf-form" action="' . esc_url($_SERVER['REQUEST_URI']) . '" method="post">
		'.((get_option('scf_option_name')['disattiva_1'] != 'disattiva_1')?'
            <label for="scf-name">' . get_option('field_name_label') . ' </label>
            <input type="text" name="scf-name" pattern="[a-zA-Z0-9 ]+" value="' . (isset($_POST["scf-name"]) ? esc_attr($_POST["scf-name"]) : '') . '" placeholder="' . get_option('field_name_placeholder') . '" required />	
		':null) .
        ((get_option('scf_option_name')['disattiva_2'] != 'disattiva_2')?'
            <label for="scf-email">' . get_option('field_email_label') . ' </label>
            <input type="email" name="scf-email" value="' . (isset($_POST["scf-email"]) ? esc_attr($_POST["scf-email"]) : '') . '" placeholder="'. get_option('field_email_placeholder') . '" required />	
        ':null) .
        ((get_option('scf_option_name')['disattiva_3'] != 'disattiva_3')?'
            <label for="scf-subject">' . get_option('field_subject_label') . ' </label>
            <input type="text" name="scf-subject" pattern="[a-zA-Z ]+" value="' . (isset($_POST["scf-subject"]) ? esc_attr($_POST["scf-subject"]) : '') . '" placeholder="'. get_option('field_subject_placeholder') . '" required />	
        ':null) .
        ((get_option('scf_option_name')['disattiva_4'] != 'disattiva_4')?'
            <label for="scf-message">' . get_option('field_message_label') . ' </label>
            <textarea rows="10" cols="35" name="scf-message" placeholder="'. get_option('field_message_placeholder') . '" required>' . (isset($_POST["scf-message"]) ? esc_attr($_POST["scf-message"]) : '') . '</textarea>		         
        ':null). '
			<input style="background-color: '. get_option('field_submit_color') . '" type="submit" name="scf-submitted" value="'. get_option('field_submit') . '" required >
		</form>
	 ';
}

function scf_send_mail(){
	if (isset($_POST['scf-submitted'])) {
		$name    = sanitize_text_field($_POST["scf-name"]);
		$email   = sanitize_email($_POST["scf-email"]);
		if (isset($_POST['scf-subject'])) {
			$subject = sanitize_text_field($_POST["scf-subject"]);
		} else {
			$subject = get_option('scf_option_name')['subject_5'];
		}
		$message = esc_textarea($_POST["scf-message"]);

		$body = "<h1>" . $subject . "</h1><br>
		<b>NOME: </b>" . $name . "<br><br>
		<b>EMAIL: </b>" . $email . "<br><br>
		<b>MESSAGGIO: </b>" . $message . "<br><br>";

		$to = get_option('scf_option_name')['email_0'];

		$headers = "MIME-Version: 1.0" . "\r\n";
		$headers .= "Content-type:text/html;charset=UTF-8" . "\r\n";
		$headers .= "Da: $name <$email>" . "\r\n";

		if (wp_mail($to, $subject, $body, $headers)) {
			echo '<div class="scf-success">Grazie per averci contattato, risponderemo il prima possibile.</div>';
		} else {
			echo '<div class="scf-error"><p>Si è verificato un problema durante l\'invio della mail, per favore riprova.</p></div>';
		}
	}
}

function scf_shortcode(){
	ob_start();
	scf_send_mail();
	scf_form();
	return ob_get_clean();
}
add_shortcode('scf', 'scf_shortcode');

if ( is_admin() )
	$scf = new Scf();

?>