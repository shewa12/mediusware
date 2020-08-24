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
    }

    public function input_field_in_profile_setting($user){

        ?>
            <h3>Instructor Reveneu</h3>
            <table class="form-table">
                <tr>
                    <th>
                        <label>Amount</label>
                    </th>
                    <td>
                        <input 
                            name="<?php echo $this->amount; ?>" 
                            type="number" 
                            class="regular-text" 
                            value="<?php echo esc_attr(get_the_author_meta($this->amount, $user->ID)); ?>"/>
                    </td>
                </tr>
                <tr>
                    <th>
                        <label>Amount Type</label>
                    </th>
                    <td>
                        <select class="regular-text" name="<?php echo $this->amount_type; ?>">
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
            </table>
        <?php
    }

    public function save_input_data($user_id){
        $amount = $_POST[$this->amount];
        $type = $_POST[$this->amount_type];

        if(!is_numeric($amount) || $amount<0 || ($type=='percent' && $amount>100)){
            // Percentage can not be greater than 100 and less than 0
            return;
        }

        update_user_meta($user_id, $this->amount, $amount);
        update_user_meta($user_id, $this->amount_type, $type);
    }

    public function payment_percent_modifier(array $data){

        /* 
            '$data' must provide following array keys
            $user_id
            $instructor_rate,
            $admin_rate,
            $instructor_amount
            $admin_amount
            $course_price_grand_total
            $commission_type 
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
                $admin_rate = ($admin_amount/$instructor_amount)*100;
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