<?php
namespace TUTOR_PRO;

if ( ! defined( 'ABSPATH' ) )
	exit;

class Instructor_Percentage
{
    private $amount = 'tutor_instructor_amount';
    private $amount_type = 'tutor_instructor_amount_type';

    private $amount_type_options = [
        'default',
        'fixed',
        'percent'
    ];

    function __construct(){
        add_filter('tutor_pro_earning_calculator', [$this, 'payment_percent_modifier']);

        add_action('edit_user_profile', [$this, 'input_field_in_profile_setting']);
        add_action('edit_user_profile_update', [$this, 'save_input_data']);

        add_filter('manage_users_columns', [$this, 'register_percentage_column']);
        add_filter('manage_users_custom_column', [$this, 'percentage_column_content'], 10, 3);

        add_action('admin_enqueue_scripts', [$this, 'register_script']);
    }

    public function register_script(){
        if(strpos(($_SERVER['REQUEST_URI'] ?? ''), 'user-edit.php')){
            // Load only if it user edit page
            wp_enqueue_script('instructor-percentage-manager-js', tutor_pro()->url.'assets/js/instructor-rate.js');
        }
    }

    public function input_field_in_profile_setting($user){

        if(!current_user_can('manage_options')){
            // Make sure only privileged user can cange payment percentage
            return;
        }

        ?>
            <h2>Instructor Setting</h2>
            <table class="form-table">
                <tr>
                    <th>
                        <label>Revenue Type</label>
                    </th>
                    <td>
                        <select id="tutor_pro_instructor_amount_type_field" class="regular-text" name="<?php echo $this->amount_type; ?>">
                            <?php
                                $amount_type = get_the_author_meta($this->amount_type, $user->ID);
                                empty($amount_type) ? $amount_type='default' : 0;

                                foreach($this->amount_type_options as $option)
                                {
                                    $selected = $amount_type==$option ? 'selected="selected"' : '';

                                    echo '<option value="'.$option.'" '.$selected.'>
                                            '.ucfirst($option).'
                                        </option>';
                                }
                            ?>
                        </select>
                    </td>
                </tr>
                <tr id="tutor_pro_instructor_amount_field">
                    <th>
                        <label>Revenue Amount</label>
                    </th>
                    <td>
                        <input 
                            name="<?php echo $this->amount; ?>" 
                            type="number" 
                            class="regular-text" 
                            value="<?php echo esc_attr(get_the_author_meta($this->amount, $user->ID)); ?>"/>
                    </td>
                </tr>
            </table>
        <?php
    }

    public function save_input_data($user_id){
        
        if(!current_user_can('manage_options')){
            // Make sure only privileged user can cange payment percentage
            return;
        }

        $amount = $_POST[$this->amount];
        $type = $_POST[$this->amount_type];

        if(!is_numeric($amount) || $amount<0 || ($type=='percent' && $amount>100)){
            // Percentage can not be greater than 100 and less than 0
            return;
        }

        update_user_meta($user_id, $this->amount, $amount);
        update_user_meta($user_id, $this->amount_type, $type);
    }

    public function register_percentage_column($columns){
        $columns[$this->amount]='Instructor Amount';
        return $columns;
    }

    public function percentage_column_content($value, $column, $user_id){

        if($column==$this->amount)
        {
            $type = get_the_author_meta($this->amount_type, $user_id);
            $amount = get_the_author_meta($this->amount, $user_id);

            if(is_numeric($amount))
            {
                $value = (($type=='percent' || $type=='fixed') ? $amount.' ' : '').ucfirst($type);
            }
        }

        return $value;
    }

    public function payment_percent_modifier(array $data){

        /* 
            '$data' must provide following array keys
            user_id
            instructor_rate
            admin_rate
            instructor_amount
            admin_amount
            course_price_grand_total
            commission_type 
        */

        extract($data);

        // $user_id is instructor ID
        $custom_amount = get_the_author_meta($this->amount, $user_id);
        $custom_type = get_the_author_meta($this->amount_type, $user_id);

        if(is_numeric($custom_amount) && $custom_amount>=0){
            if($custom_type=='fixed'){

                $commission_type = 'fixed';

                // Make sure custom amount is less than or equal to grand total
                $custom_amount>$course_price_grand_total ? $custom_amount=$course_price_grand_total : 0;

                // Set clculated amount
                $instructor_amount = $custom_amount;
                $admin_amount = $course_price_grand_total-$instructor_amount;
                $admin_amount<0 ? $admin_amount=0 : 0;

                // Set calculated rate
                $admin_rate = ($admin_amount/$course_price_grand_total)*100;
                $instructor_rate = 100-$admin_rate;
            }
            else if($custom_type=='percent'){

                $commission_type='percent';

                // Set calculated rate
                $instructor_rate = $custom_amount;
                $admin_rate = 100-$instructor_rate;

                // Set calculated amount
                $instructor_amount = ($instructor_rate/100)*$course_price_grand_total;
                $admin_amount = $course_price_grand_total-$instructor_amount;
            }
        }
        
        return [
            'user_id' => $user_id,
            'instructor_rate' => $instructor_rate,
            'admin_rate' => $admin_rate,
            'instructor_amount' => $instructor_amount,
            'admin_amount' => $admin_amount,
            'course_price_grand_total' => $course_price_grand_total,
            'commission_type' => $commission_type
        ];
    }
}