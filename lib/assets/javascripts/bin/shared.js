function latepoint_timestamped_ajaxurl(){
    let url = latepoint_helper.ajaxurl;
    let timestamp = Date.now();

    // Check if the URL already has GET parameters
    if (url.includes('?')) {
        return `${url}&t=${timestamp}`;
    } else {
        return `${url}?t=${timestamp}`;
    }
}

function latepoint_random_generator() {
  var S4 = function () {
    return (((1 + Math.random()) * 0x10000) | 0).toString(16).substring(1);
  };
  return (S4() + S4() + "-" + S4() + "-" + S4() + "-" + S4() + "-" + S4() + S4() + S4());
}

function latepoint_validate_form($form) {
  let errors = [];
  $form.find('select[data-os-validate], input[data-os-validate], textarea[data-os-validate]').each(function () {
    let validations = jQuery(this).data('os-validate').split(' ');
    let $input = jQuery(this);
    let label = $input.closest('.os-form-group').find('label').text();
    let field_has_errors = false;
    if (validations) {
      for (let i = 0; i < validations.length; i++) {
        switch (validations[i]) {
          case 'presence':
            if($input.is(':checkbox')){
              if (!$input.is(':checked')) {
                errors.push({message: label + ' ' + latepoint_helper.msg_validation_presence_checkbox});
                field_has_errors = true;
              }
            }else{
              if (!$input.val()) {
                errors.push({message: label + ' ' + latepoint_helper.msg_validation_presence});
                field_has_errors = true;
              }
            }
            break;
          case 'phone':
            if (!window.intlTelInputGlobals.getInstance($input[0]).isValidNumber()) {
              errors.push({message: label + ' ' + latepoint_helper.msg_validation_invalid});
              field_has_errors = true;
            }
            break;
        }
      }
    }
    if (field_has_errors) {
      $input.closest('.os-form-group').addClass('os-invalid');
    } else {
      $input.closest('.os-form-group').removeClass('os-invalid');
    }
  });
  return errors;
}

function latepoint_create_form_data_from_non_form_element($elem) {
  let formData = new FormData();
  // create objecte from all input fields that are inside of the element
  let fields = $elem.find('select, input, textarea').serializeArray();
  if (fields) {
    fields.forEach(field => formData.append(field.name, field.value));
  }
  return formData;
}

function latepoint_create_form_data($form, route_name = false, extra_params = false) {
  let form_data = new FormData();
  let params = new FormData($form[0]);

  if (extra_params) {
    Object.keys(extra_params).forEach(key => {
      params.set(key, extra_params[key]);
    });
  }

  // get values from phone number fields
  if (('intlTelInputGlobals' in window) && ('intlTelInputUtils' in window)) {
    $form.find('input.os-mask-phone').each(function () {
      const phoneInputName = this.getAttribute('name');
      const phoneInputValue = window.intlTelInputGlobals.getInstance(this).getNumber(window.intlTelInputUtils.numberFormat.E164);
      // override value generated automatically by formdata with a formatted value of a phone field with country code
      params.set(phoneInputName, phoneInputValue);
    });
  }

  form_data.append('params', latepoint_formdata_to_url_encoded_string(params));
  form_data.append('action', latepoint_helper.route_action);
  form_data.append('route_name', route_name ? route_name : $form.data('route-name'));
  form_data.append('layout', 'none');
  form_data.append('return_format', 'json');

  let file_data;
  // put file data into main form_data object, since we can't send them in "params" string
  $form.find('input[type="file"]').each(function () {
    file_data = this.files; // get multiple files from input file
    let file_name = this.getAttribute("name");
    for (let i = 0; i < file_data.length; i++) {
      form_data.append(file_name + '[]', file_data[i]);
    }
  });
  return form_data;
}

function latepoint_mask_timefield($elem) {
  if (jQuery().inputmask) {
    $elem.inputmask({
      'mask': '99:99',
      'placeholder': 'HH:MM'
    });
  }
}

function latepoint_formdata_to_url_encoded_string(form_data) {
  let filtered_form_data = new FormData();
  // remove file fields from params, so we can serialize it into string,
  // !important, this will not include file fields into the form_data, so you have to include them manually, see latepoint_create_form_data() that does it
  // note: we don't use form_data.remove(key) on original object because we might want to preserve it
  for (const [key, value] of form_data) {
    if (value instanceof File) continue;
    if (key.slice(-2) === '[]') {
      // expecting array, append
      filtered_form_data.append(key, value);
    } else {
      filtered_form_data.set(key, value);
    }
  }
  return new URLSearchParams(filtered_form_data).toString();
}

function latepoint_mask_percent($elem) {
  if (jQuery().inputmask) {
    $elem.inputmask({
      'alias': 'decimal',
      'radixPoint': latepoint_helper.decimal_separator,
      'digits': 4,
      'digitsOptional': false,
      'suffix': '%',
      'placeholder': '0',
      'rightAlign': false
    });
  }
}

function latepoint_mask_minutes($elem) {
  if (jQuery().inputmask) {
    $elem.inputmask({
      'removeMaskOnSubmit': true,
      'alias': 'numeric',
      'digits': 0,
      'suffix': latepoint_helper.msg_minutes_suffix,
      'placeholder': '0',
      'rightAlign': false
    });
  }
}


function latepoint_mask_money($elem) {
  if (jQuery().inputmask) {
    $elem.inputmask({
      'alias': 'currency',
      'groupSeparator': latepoint_helper.thousand_separator,
      'radixPoint': latepoint_helper.decimal_separator,
      'digits': latepoint_helper.number_of_decimals,
      'digitsOptional': false,
      'prefix': latepoint_helper.currency_symbol_before ? latepoint_helper.currency_symbol_before + ' ' : '',
      'suffix': latepoint_helper.currency_symbol_after ? ' ' + latepoint_helper.currency_symbol_after : '',
      'placeholder': '0',
      'rightAlign': false
    });
  }
}

function latepoint_mask_date($elem) {
  if (jQuery().inputmask) {
    $elem.inputmask({
      'alias': 'datetime',
      'inputFormat': latepoint_helper.date_format_for_js
    });
  }
}

function latepoint_init_phone_masking_from_placeholder($input) {
  if (!latepoint_helper.mask_phone_number_fields) return;
  let format = $input.attr('placeholder');
  if (format && jQuery().inputmask) {
    $input.inputmask(format.replace(/[0-9]/g, 9));
  }
}

function latepoint_mask_phone($elem) {
  let jsElem = $elem[0];

  // First priority is to prevent duplicates (common in non-document.body contexts)
  if (jsElem && !window.intlTelInputGlobals.getInstance(jsElem)) {
    let dropdownContainer = document.body;

    let onlyCountries = JSON.parse(latepoint_helper.included_phone_countries);
    // Remedy a quirk with json_encode(EMPTY_ARRAY)
    if (onlyCountries.length === 1 && onlyCountries[0] === "") {
      onlyCountries = [];
    }
    const preferredCountries = onlyCountries.length ? [] : window.intlTelInputGlobals.defaults.preferredCountries;

    // remove country name in english and only use names in country language
    var countryData = window.intlTelInputGlobals.getCountryData();

    for (var i = 0; i < countryData.length; i++) {
      var country = countryData[i];
      country.name = country.name.replace(/ *\([^)]*\) */g, "");
    }

    let defaultCountryCode = latepoint_helper.default_phone_country;
    if (onlyCountries.length && !onlyCountries.includes(defaultCountryCode)) {
      defaultCountryCode = onlyCountries[0];
    }


    let iti = window.intlTelInput(jsElem, {
      dropdownContainer: dropdownContainer,
      formatOnDisplay: true,
      nationalMode: true,
      autoPlaceholder: 'aggressive',
      initialCountry: defaultCountryCode,
      geoIpLookup: function (callback) {
        const cookieName = 'latepoint_phone_country';

        if (latepoint_has_cookie(cookieName)) {
          callback(latepoint_get_cookie(cookieName));
        } else {
          jQuery.get('https://ipinfo.io', function () {
          }, 'jsonp').always(function (response) {
            // Sensible default
            let countryCode = defaultCountryCode;

            if (response && response.country) {
              countryCode = response.country.toLowerCase();
              latepoint_set_cookie(cookieName, countryCode);
            }
            callback(countryCode);
          })
        }
      },
      allowDropdown: onlyCountries.length != 1,
      onlyCountries: onlyCountries,
      preferredCountries: preferredCountries,
      separateDialCode: latepoint_helper.is_enabled_show_dial_code_with_flag
    });

    iti.promise.then(function () {
      latepoint_init_phone_masking_from_placeholder($elem);
    });


    $elem.on("countrychange", function (event) {
      latepoint_init_phone_masking_from_placeholder(jQuery(this));
    });
  }
}

function latepoint_show_booking_end_time() {
  return (latepoint_helper.show_booking_end_time == 'yes');
}

function latepoint_set_cookie(name, value, days) {
  let date = new Date;
  date.setTime(date.getTime() + 24 * 60 * 60 * 1000 * days);
  document.cookie = name + "=" + value + ";path=/;expires=" + date.toGMTString();
}

function latepoint_get_cookie(name) {
  let cookie = document.cookie.match('(^|;) ?' + name + '=([^;]*)(;|$)');
  return cookie ? cookie[2] : null;
}

function latepoint_has_cookie(name) {
  return latepoint_get_cookie(name) !== null;
}

function latepoint_delete_cookie(name) {
  latepoint_set_cookie(name, '', -1);
}