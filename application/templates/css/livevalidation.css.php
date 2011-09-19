/********************************* LiveValidation *************************************/

.LV_valid {
    color:#00CC00;
}
	
.LV_invalid {
	color:#CC0000;
}
	
.LV_validation_message{
    font-weight:bold;
    margin:0 0 0 5px;
}
    
.LV_valid_field,
input.LV_valid_field:hover, 
input.LV_valid_field:active,
textarea.LV_valid_field:hover, 
textarea.LV_valid_field:active,
.fieldWithErrors input.LV_valid_field,
.fieldWithErrors textarea.LV_valid_field {
    border: 1px solid #00CC00;
}
    
.LV_invalid_field, 
input.LV_invalid_field:hover, 
input.LV_invalid_field:active,
textarea.LV_invalid_field:hover, 
textarea.LV_invalid_field:active,
.fieldWithErrors input.LV_invalid_field,
.fieldWithErrors textarea.LV_invalid_field {
    border: 1px solid #CC0000;
}
