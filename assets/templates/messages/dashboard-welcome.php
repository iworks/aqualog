
<?php
/**
 * Create aquarium first message template.
 *
 * @package Aqualog
 * @subpackage Templates
 * @since 1.0.0
 */

defined( 'ABSPATH' ) || exit;
?>
<div id="iworks-aqualog-dashboard-message" class="notice notice-info is-dismissible">
    <h3><?php esc_html_e( 'Welcome to AquaLog!', 'aqualog' ); ?></h3>
    <p>
        <?php 
        esc_html_e( 'Thank you for installing AquaLog! This powerful plugin helps you manage and track your water-related activities with ease. Here are some quick tips to get you started:', 'aqualog' ); 
        ?>
    </p>
    <ul>
        <li><?php esc_html_e( 'Navigate to the AquaLog settings page to configure your preferences', 'aqualog' ); ?></li>
        <li><?php esc_html_e( 'Add your first water entry to start tracking your daily consumption', 'aqualog' ); ?></li>
        <li><?php esc_html_e( 'Check out the analytics dashboard to view your water usage patterns', 'aqualog' ); ?></li>
        <li><?php esc_html_e( 'Set up reminders to help you stay hydrated throughout the day', 'aqualog' ); ?></li>
    </ul>
    <p>
        <strong><?php esc_html_e( 'Pro Tip:', 'aqualog' ); ?></strong> 
        <?php esc_html_e( 'Regular water tracking can improve your health and wellbeing. Make it a daily habit!', 'aqualog' ); ?>
    </p>
    <p>
        <a href="<?php echo esc_url( admin_url( 'admin.php?page=' . $this->dir . '/admin/index.php' ) ); ?>" class="button button-primary">
            <?php esc_html_e( 'Get Started', 'aqualog' ); ?>
        </a>
        <a href="<?php echo esc_url( _x( 'https://wordpress.org/plugins/aqualog/', 'plugin homepage', 'aqualog' ) ); ?>" target="_blank" class="button">
            <?php esc_html_e( 'Learn More', 'aqualog' ); ?>
        </a>
    </p>
</div>