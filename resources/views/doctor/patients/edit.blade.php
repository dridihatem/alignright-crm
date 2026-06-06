<x-app-layout>
    @push('styles')
    <link rel="stylesheet" href="{{ asset('assets/vendor/libs/flatpickr/flatpickr.css') }}" />
    <link rel="stylesheet" href="{{ asset('assets/vendor/libs/select2/select2.css') }}" />
    @endpush
    <div class="container-xxl flex-grow-1 container-p-y">
        <div class="row g-6">
            <div class="col-md-12 col-xxl-12 mb-6">
                <div class="card">

                    <div class="card-header">
                        <h5 class="mb-1">{{ __('master.edit_patient') }}</h5>
                    </div>

                    <div class="card-body"> 
                        <form action="{{ route('doctor.patients.update', $patient->reference) }}" method="post">
                            @csrf
                            @method('PUT')
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="gender" class="form-label">{{ __('master.gender') }}</label>    
                                        <select class="form-select select2" id="gender" name="gender" >
                                            <option value="">{{ __('master.select_gender') }}</option>
                                            <option value="male" {{ $patient->gender == 'male' ? 'selected' : '' }}>{{ __('master.male') }}</option>
                                            <option value="female" {{ $patient->gender == 'female' ? 'selected' : '' }}>{{ __('master.female') }}</option>
                                        </select>
                                    </div> 
                                    <div class="mb-3">
                                        <label for="reference" class="form-label">{{ __('master.reference') }}</label>    
                                        <input type="text" class="form-control" id="reference" name="reference" required value="{{old('reference', $patient->reference) }}">
                                    </div>
                                    <div class="mb-3">
                                        <label for="name" class="form-label">{{ __('master.name') }}</label>    
                                        <input type="text" class="form-control" id="name" name="name" required value="{{old('name', $patient->name) }}">
                                    </div>
                                    <div class="mb-3">
                                        <label for="surname" class="form-label">{{ __('master.surname') }}</label>    
                                        <input type="text" class="form-control" id="surname" name="surname" required value="{{old('surname', $patient->surname) }}">
                                    </div>
                                    <div class="mb-3">
                                        <label for="email" class="form-label">{{ __('master.email') }}</label>    
                                        <input type="email" class="form-control" id="email" name="email" required value="{{old('email', $patient->email) }}">
                                    </div>
                                    <div class="mb-3">
                                        <label for="phone" class="form-label">{{ __('master.phone') }}</label>    
                                        <input type="text" class="form-control" id="phone" name="phone" required value="{{old('phone', $patient->phone) }}">
                                    </div>
                                    
                                  
                                   
                                   
                                </div>
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="address" class="form-label">{{ __('master.address') }}</label>    
                                        <input type="text" class="form-control" id="address" name="address" required value="{{old('address', $patient->address) }}">
                                    </div>
                                    <div class="mb-3">
                                        <label for="city" class="form-label">{{ __('master.city') }}</label>    
                                        <input type="text" class="form-control" id="city" name="city" required value="{{old('city', $patient->city) }}">
                                    </div>
                                    <div class="mb-3">
                                        <label for="flatpickr-date" class="form-label">{{ __('master.patient_birthday') }}</label>    
                                        <input type="text" class="form-control" id="flatpickr-date" name="birthday" required value="{{old('birthday', $patient->birthday) }}">
                                    </div>
                                    <div class="mb-3">
                                        <label class="form-label" for="country">{{ __('master.country') }}</label>
                                                        <select class="form-select select2" id="country" name="country"  >
                                                            <option value="">{{ __('master.select_country') }}</option>
                                                            <option value="AF" {{ $patient->country == 'AF' ? 'selected' : '' }}>Afghanistan</option>
                                                            <option value="AL" {{ $patient->country == 'AL' ? 'selected' : '' }}>Albania</option>
                                                            <option value="DZ" {{ $patient->country == 'DZ' ? 'selected' : '' }}>Algeria</option>
                                                            <option value="AD" {{ $patient->country == 'AD' ? 'selected' : '' }}>Andorra</option>
                                                            <option value="AO" {{ $patient->country == 'AO' ? 'selected' : '' }}>Angola</option>
                                                            <option value="AG" {{ $patient->country == 'AG' ? 'selected' : '' }}>Antigua and Barbuda</option>
                                                            <option value="AR" {{ $patient->country == 'AR' ? 'selected' : '' }}>Argentina</option>
                                                            <option value="AM" {{ $patient->country == 'AM' ? 'selected' : '' }}>Armenia</option>
                                                            <option value="AU" {{ $patient->country == 'AU' ? 'selected' : '' }}>Australia</option>
                                                            <option value="AT" {{ $patient->country == 'AT' ? 'selected' : '' }}>Austria</option>
                                                            <option value="AZ" {{ $patient->country == 'AZ' ? 'selected' : '' }}>Azerbaijan</option>
                                                            <option value="BS" {{ $patient->country == 'BS' ? 'selected' : '' }}>Bahamas</option>
                                                            <option value="BH" {{ $patient->country == 'BH' ? 'selected' : '' }}>Bahrain</option>
                                                            <option value="BD" {{ $patient->country == 'BD' ? 'selected' : '' }}>Bangladesh</option>
                                                            <option value="BB" {{ $patient->country == 'BB' ? 'selected' : '' }}>Barbados</option>
                                                            <option value="BY" {{ $patient->country == 'BY' ? 'selected' : '' }}>Belarus</option>
                                                            <option value="BE" {{ $patient->country == 'BE' ? 'selected' : '' }}>Belgium</option>
                                                            <option value="BZ" {{ $patient->country == 'BZ' ? 'selected' : '' }}>Belize</option>
                                                            <option value="BJ" {{ $patient->country == 'BJ' ? 'selected' : '' }}>Benin</option>
                                                            <option value="BT" {{ $patient->country == 'BT' ? 'selected' : '' }}>Bhutan</option>
                                                            <option value="BO" {{ $patient->country == 'BO' ? 'selected' : '' }}>Bolivia</option>
                                                            <option value="BA" {{ $patient->country == 'BA' ? 'selected' : '' }}>Bosnia and Herzegovina</option>
                                                            <option value="BW" {{ $patient->country == 'BW' ? 'selected' : '' }}>Botswana</option>
                                                            <option value="BR" {{ $patient->country == 'BR' ? 'selected' : '' }}>Brazil</option>
                                                            <option value="BN" {{ $patient->country == 'BN' ? 'selected' : '' }}>Brunei</option>
                                                            <option value="BG" {{ $patient->country == 'BG' ? 'selected' : '' }}>Bulgaria</option>
                                                            <option value="BF" {{ $patient->country == 'BF' ? 'selected' : '' }}>Burkina Faso</option>
                                                            <option value="BI" {{ $patient->country == 'BI' ? 'selected' : '' }}>Burundi</option>
                                                            <option value="KH" {{ $patient->country == 'KH' ? 'selected' : '' }}>Cambodia</option>
                                                            <option value="CM" {{ $patient->country == 'CM' ? 'selected' : '' }}>Cameroon</option>
                                                            <option value="CA" {{ $patient->country == 'CA' ? 'selected' : '' }}>Canada</option>
                                                            <option value="CV" {{ $patient->country == 'CV' ? 'selected' : '' }}>Cape Verde</option>
                                                            <option value="CF" {{ $patient->country == 'CF' ? 'selected' : '' }}>Central African Republic</option>
                                                            <option value="TD" {{ $patient->country == 'TD' ? 'selected' : '' }}>Chad</option>
                                                            <option value="CL" {{ $patient->country == 'CL' ? 'selected' : '' }}>Chile</option>
                                                            <option value="CN" {{ $patient->country == 'CN' ? 'selected' : '' }}>China</option>
                                                            <option value="CO" {{ $patient->country == 'CO' ? 'selected' : '' }}>Colombia</option>
                                                            <option value="KM" {{ $patient->country == 'KM' ? 'selected' : '' }}>Comoros</option>
                                                            <option value="CG" {{ $patient->country == 'CG' ? 'selected' : '' }}>Congo</option>
                                                            <option value="CR" {{ $patient->country == 'CR' ? 'selected' : '' }}>Costa Rica</option>
                                                            <option value="HR" {{ $patient->country == 'HR' ? 'selected' : '' }}>Croatia</option>
                                                            <option value="CU" {{ $patient->country == 'CU' ? 'selected' : '' }}>Cuba</option>
                                                            <option value="CY" {{ $patient->country == 'CY' ? 'selected' : '' }}>Cyprus</option>
                                                            <option value="CZ" {{ $patient->country == 'CZ' ? 'selected' : '' }}>Czech Republic</option>
                                                            <option value="DK" {{ $patient->country == 'DK' ? 'selected' : '' }}>Denmark</option>
                                                            <option value="DJ" {{ $patient->country == 'DJ' ? 'selected' : '' }}>Djibouti</option>
                                                            <option value="DM" {{ $patient->country == 'DM' ? 'selected' : '' }}>Dominica</option>
                                                            <option value="DO" {{ $patient->country == 'DO' ? 'selected' : '' }}>Dominican Republic</option>
                                                            <option value="EC" {{ $patient->country == 'EC' ? 'selected' : '' }}>Ecuador</option>
                                                            <option value="EG" {{ $patient->country == 'EG' ? 'selected' : '' }}>Egypt</option>
                                                            <option value="SV" {{ $patient->country == 'SV' ? 'selected' : '' }}>El Salvador</option>
                                                            <option value="GQ" {{ $patient->country == 'GQ' ? 'selected' : '' }}>Equatorial Guinea</option>
                                                            <option value="ER" {{ $patient->country == 'ER' ? 'selected' : '' }}>Eritrea</option>
                                                            <option value="EE" {{ $patient->country == 'EE' ? 'selected' : '' }}>Estonia</option>
                                                            <option value="ET" {{ $patient->country == 'ET' ? 'selected' : '' }}>Ethiopia</option>
                                                            <option value="FJ" {{ $patient->country == 'FJ' ? 'selected' : '' }}>Fiji</option>
                                                            <option value="FI" {{ $patient->country == 'FI' ? 'selected' : '' }}>Finland</option>
                                                            <option value="FR" {{ $patient->country == 'FR' ? 'selected' : '' }}>France</option>
                                                            <option value="GA" {{ $patient->country == 'GA' ? 'selected' : '' }}>Gabon</option>
                                                            <option value="GM" {{ $patient->country == 'GM' ? 'selected' : '' }}>Gambia</option>
                                                            <option value="GE" {{ $patient->country == 'GE' ? 'selected' : '' }}>Georgia</option>
                                                            <option value="DE" {{ $patient->country == 'DE' ? 'selected' : '' }}>Germany</option>
                                                            <option value="GH" {{ $patient->country == 'GH' ? 'selected' : '' }}>Ghana</option>
                                                            <option value="GR" {{ $patient->country == 'GR' ? 'selected' : '' }}>Greece</option>
                                                            <option value="GD" {{ $patient->country == 'GD' ? 'selected' : '' }}>Grenada</option>
                                                            <option value="GT" {{ $patient->country == 'GT' ? 'selected' : '' }}>Guatemala</option>
                                                            <option value="GN" {{ $patient->country == 'GN' ? 'selected' : '' }}>Guinea</option>
                                                            <option value="GW" {{ $patient->country == 'GW' ? 'selected' : '' }}>Guinea-Bissau</option>
                                                            <option value="GY" {{ $patient->country == 'GY' ? 'selected' : '' }}>Guyana</option>
                                                            <option value="HT" {{ $patient->country == 'HT' ? 'selected' : '' }}>Haiti</option>
                                                            <option value="HN" {{ $patient->country == 'HN' ? 'selected' : '' }}>Honduras</option>
                                                            <option value="HU" {{ $patient->country == 'HU' ? 'selected' : '' }}>Hungary</option>
                                                            <option value="IS" {{ $patient->country == 'IS' ? 'selected' : '' }}>Iceland</option>
                                                            <option value="IN" {{ $patient->country == 'IN' ? 'selected' : '' }}>India</option>
                                                            <option value="ID" {{ $patient->country == 'ID' ? 'selected' : '' }}>Indonesia</option>
                                                            <option value="IR" {{ $patient->country == 'IR' ? 'selected' : '' }}>Iran</option>
                                                            <option value="IQ" {{ $patient->country == 'IQ' ? 'selected' : '' }}>Iraq</option>
                                                            <option value="IE" {{ $patient->country == 'IE' ? 'selected' : '' }}>Ireland</option>
                                                            <option value="IL" {{ $patient->country == 'IL' ? 'selected' : '' }}>Israel</option>
                                                            <option value="IT" {{ $patient->country == 'IT' ? 'selected' : '' }}>Italy</option>
                                                            <option value="JM" {{ $patient->country == 'JM' ? 'selected' : '' }}>Jamaica</option>
                                                            <option value="JP" {{ $patient->country == 'JP' ? 'selected' : '' }}>Japan</option>
                                                            <option value="JO" {{ $patient->country == 'JO' ? 'selected' : '' }}>Jordan</option>
                                                            <option value="KZ" {{ $patient->country == 'KZ' ? 'selected' : '' }}>Kazakhstan</option>
                                                            <option value="KE" {{ $patient->country == 'KE' ? 'selected' : '' }}>Kenya</option>
                                                            <option value="KI" {{ $patient->country == 'KI' ? 'selected' : '' }}>Kiribati</option>
                                                            <option value="KP" {{ $patient->country == 'KP' ? 'selected' : '' }}>North Korea</option>
                                                            <option value="KR" {{ $patient->country == 'KR' ? 'selected' : '' }}>South Korea</option>
                                                            <option value="KW" {{ $patient->country == 'KW' ? 'selected' : '' }}>Kuwait</option>
                                                            <option value="KG" {{ $patient->country == 'KG' ? 'selected' : '' }}>Kyrgyzstan</option>
                                                            <option value="LA" {{ $patient->country == 'LA' ? 'selected' : '' }}>Laos</option>
                                                            <option value="LV" {{ $patient->country == 'LV' ? 'selected' : '' }}>Latvia</option>
                                                            <option value="LB" {{ $patient->country == 'LB' ? 'selected' : '' }}>Lebanon</option>
                                                            <option value="LS" {{ $patient->country == 'LS' ? 'selected' : '' }}>Lesotho</option>
                                                            <option value="LR" {{ $patient->country == 'LR' ? 'selected' : '' }}>Liberia</option>
                                                            <option value="LY" {{ $patient->country == 'LY' ? 'selected' : '' }}>Libya</option>
                                                            <option value="LI" {{ $patient->country == 'LI' ? 'selected' : '' }}>Liechtenstein</option>
                                                            <option value="LT" {{ $patient->country == 'LT' ? 'selected' : '' }}>Lithuania</option>
                                                            <option value="LU" {{ $patient->country == 'LU' ? 'selected' : '' }}>Luxembourg</option>
                                                            <option value="MK" {{ $patient->country == 'MK' ? 'selected' : '' }}>Macedonia</option>
                                                            <option value="MG" {{ $patient->country == 'MG' ? 'selected' : '' }}>Madagascar</option>
                                                            <option value="MW" {{ $patient->country == 'MW' ? 'selected' : '' }}>Malawi</option>
                                                            <option value="MY" {{ $patient->country == 'MY' ? 'selected' : '' }}>Malaysia</option>
                                                            <option value="MV" {{ $patient->country == 'MV' ? 'selected' : '' }}>Maldives</option>
                                                            <option value="ML" {{ $patient->country == 'ML' ? 'selected' : '' }}>Mali</option>
                                                            <option value="MT" {{ $patient->country == 'MT' ? 'selected' : '' }}>Malta</option>
                                                            <option value="MH" {{ $patient->country == 'MH' ? 'selected' : '' }}>Marshall Islands</option>
                                                            <option value="MR" {{ $patient->country == 'MR' ? 'selected' : '' }}>Mauritania</option>
                                                            <option value="MU" {{ $patient->country == 'MU' ? 'selected' : '' }}>Mauritius</option>
                                                            <option value="MX" {{ $patient->country == 'MX' ? 'selected' : '' }}    >Mexico</option>
                                                            <option value="FM" {{ $patient->country == 'FM' ? 'selected' : '' }}>Micronesia</option>
                                                            <option value="MD" {{ $patient->country == 'MD' ? 'selected' : '' }}>Moldova</option>
                                                            <option value="MC" {{ $patient->country == 'MC' ? 'selected' : '' }}>Monaco</option>
                                                            <option value="MN" {{ $patient->country == 'MN' ? 'selected' : '' }}    >Mongolia</option>
                                                            <option value="ME" {{ $patient->country == 'ME' ? 'selected' : '' }}>Montenegro</option>
                                                            <option value="MA" {{ $patient->country == 'MA' ? 'selected' : '' }}>Morocco</option>
                                                            <option value="MZ" {{ $patient->country == 'MZ' ? 'selected' : '' }}>Mozambique</option>
                                                            <option value="MM" {{ $patient->country == 'MM' ? 'selected' : '' }}>Myanmar</option>
                                                            <option value="NA" {{ $patient->country == 'NA' ? 'selected' : '' }}>Namibia</option>
                                                            <option value="NR" {{ $patient->country == 'NR' ? 'selected' : '' }}>Nauru</option>
                                                            <option value="NP" {{ $patient->country == 'NP' ? 'selected' : '' }}>Nepal</option>
                                                            <option value="NL" {{ $patient->country == 'NL' ? 'selected' : '' }}>Netherlands</option>
                                                            <option value="NZ" {{ $patient->country == 'NZ' ? 'selected' : '' }}>New Zealand</option>
                                                            <option value="NI" {{ $patient->country == 'NI' ? 'selected' : '' }}>Nicaragua</option>
                                                            <option value="NE" {{ $patient->country == 'NE' ? 'selected' : '' }}>Niger</option>
                                                            <option value="NG" {{ $patient->country == 'NG' ? 'selected' : '' }}>Nigeria</option>
                                                            <option value="NO" {{ $patient->country == 'NO' ? 'selected' : '' }}>Norway</option>
                                                            <option value="OM" {{ $patient->country == 'OM' ? 'selected' : '' }}>Oman</option>
                                                            <option value="PK" {{ $patient->country == 'PK' ? 'selected' : '' }}>Pakistan</option>
                                                            <option value="PW" {{ $patient->country == 'PW' ? 'selected' : '' }}>Palau</option>
                                                            <option value="PS" {{ $patient->country == 'PS' ? 'selected' : '' }}>Palestine</option>
                                                            <option value="PA" {{ $patient->country == 'PA' ? 'selected' : '' }}>Panama</option>
                                                            <option value="PG" {{ $patient->country == 'PG' ? 'selected' : '' }}>Papua New Guinea</option>
                                                            <option value="PY" {{ $patient->country == 'PY' ? 'selected' : '' }}>Paraguay</option>
                                                            <option value="PE" {{ $patient->country == 'PE' ? 'selected' : '' }}>Peru</option>
                                                            <option value="PH" {{ $patient->country == 'PH' ? 'selected' : '' }}>Philippines</option>
                                                            <option value="PL" {{ $patient->country == 'PL' ? 'selected' : '' }}>Poland</option>
                                                            <option value="PT" {{ $patient->country == 'PT' ? 'selected' : '' }}>Portugal</option>
                                                            <option value="QA" {{ $patient->country == 'QA' ? 'selected' : '' }}>Qatar</option>
                                                            <option value="RO" {{ $patient->country == 'RO' ? 'selected' : '' }}>Romania</option>
                                                            <option value="RU" {{ $patient->country == 'RU' ? 'selected' : '' }}>Russia</option>
                                                            <option value="RW" {{ $patient->country == 'RW' ? 'selected' : '' }}>Rwanda</option>
                                                            <option value="KN" {{ $patient->country == 'KN' ? 'selected' : '' }}>Saint Kitts and Nevis</option>
                                                            <option value="LC" {{ $patient->country == 'LC' ? 'selected' : '' }}>Saint Lucia</option>
                                                            <option value="VC" {{ $patient->country == 'VC' ? 'selected' : '' }}>Saint Vincent and the Grenadines</option>
                                                            <option value="WS" {{ $patient->country == 'WS' ? 'selected' : '' }}>Samoa</option>
                                                            <option value="SM" {{ $patient->country == 'SM' ? 'selected' : '' }}>San Marino</option>
                                                            <option value="ST" {{ $patient->country == 'ST' ? 'selected' : '' }}>Sao Tome and Principe</option>
                                                            <option value="SA" {{ $patient->country == 'SA' ? 'selected' : '' }}>Saudi Arabia</option>
                                                            <option value="SN" {{ $patient->country == 'SN' ? 'selected' : '' }}>Senegal</option>
                                                            <option value="RS" {{ $patient->country == 'RS' ? 'selected' : '' }}>Serbia</option>
                                                            <option value="SC" {{ $patient->country == 'SC' ? 'selected' : '' }}>Seychelles</option>
                                                            <option value="SL" {{ $patient->country == 'SL' ? 'selected' : '' }}>Sierra Leone</option>
                                                            <option value="SG" {{ $patient->country == 'SG' ? 'selected' : '' }}>Singapore</option>
                                                            <option value="SK" {{ $patient->country == 'SK' ? 'selected' : '' }}>Slovakia</option>
                                                            <option value="SI" {{ $patient->country == 'SI' ? 'selected' : '' }}>Slovenia</option>
                                                            <option value="SB" {{ $patient->country == 'SB' ? 'selected' : '' }}>Solomon Islands</option>
                                                            <option value="SO" {{ $patient->country == 'SO' ? 'selected' : '' }}>Somalia</option>
                                                            <option value="ZA" {{ $patient->country == 'ZA' ? 'selected' : '' }}>South Africa</option>
                                                            <option value="SS" {{ $patient->country == 'SS' ? 'selected' : '' }}>South Sudan</option>
                                                            <option value="ES" {{ $patient->country == 'ES' ? 'selected' : '' }}>Spain</option>
                                                            <option value="LK" {{ $patient->country == 'LK' ? 'selected' : '' }}>Sri Lanka</option>
                                                            <option value="SD" {{ $patient->country == 'SD' ? 'selected' : '' }}>Sudan</option>
                                                            <option value="SR" {{ $patient->country == 'SR' ? 'selected' : '' }}>Suriname</option>
                                                            <option value="SZ" {{ $patient->country == 'SZ' ? 'selected' : '' }}>Swaziland</option>
                                                            <option value="SE" {{ $patient->country == 'SE' ? 'selected' : '' }}>Sweden</option>
                                                            <option value="CH" {{ $patient->country == 'CH' ? 'selected' : '' }}>Switzerland</option>
                                                            <option value="SY" {{ $patient->country == 'SY' ? 'selected' : '' }}>Syria</option>
                                                            <option value="TW" {{ $patient->country == 'TW' ? 'selected' : '' }}>Taiwan</option>
                                                            <option value="TJ" {{ $patient->country == 'TJ' ? 'selected' : '' }}>Tajikistan</option>
                                                            <option value="TZ" {{ $patient->country == 'TZ' ? 'selected' : '' }}>Tanzania</option>
                                                            <option value="TH" {{ $patient->country == 'TH' ? 'selected' : '' }}>Thailand</option>
                                                            <option value="TL" {{ $patient->country == 'TL' ? 'selected' : '' }}>Timor-Leste</option>
                                                            <option value="TG" {{ $patient->country == 'TG' ? 'selected' : '' }}>Togo</option>
                                                            <option value="TO" {{ $patient->country == 'TO' ? 'selected' : '' }}>Tonga</option>
                                                            <option value="TT" {{ $patient->country == 'TT' ? 'selected' : '' }}>Trinidad and Tobago</option>
                                                            <option value="TN" {{ $patient->country == 'TN' ? 'selected' : '' }}>Tunisia</option>
                                                            <option value="TR" {{ $patient->country == 'TR' ? 'selected' : '' }}>Turkey</option>
                                                            <option value="TM" {{ $patient->country == 'TM' ? 'selected' : '' }}>Turkmenistan</option>
                                                            <option value="TV" {{ $patient->country == 'TV' ? 'selected' : '' }}>Tuvalu</option>
                                                            <option value="UG" {{ $patient->country == 'UG' ? 'selected' : '' }}>Uganda</option>
                                                            <option value="UA" {{ $patient->country == 'UA' ? 'selected' : '' }}>Ukraine</option>
                                                            <option value="AE" {{ $patient->country == 'AE' ? 'selected' : '' }}>United Arab Emirates</option>
                                                            <option value="GB" {{ $patient->country == 'GB' ? 'selected' : '' }}>United Kingdom</option>
                                                            <option value="US" {{ $patient->country == 'US' ? 'selected' : '' }}>United States</option>
                                                            <option value="UY" {{ $patient->country == 'UY' ? 'selected' : '' }}>Uruguay</option>
                                                            <option value="UZ" {{ $patient->country == 'UZ' ? 'selected' : '' }}>Uzbekistan</option>
                                                            <option value="VU" {{ $patient->country == 'VU' ? 'selected' : '' }}>Vanuatu</option>
                                                            <option value="VA" {{ $patient->country == 'VA' ? 'selected' : '' }}>Vatican City</option>
                                                            <option value="VE" {{ $patient->country == 'VE' ? 'selected' : '' }}>Venezuela</option>
                                                            <option value="VN" {{ $patient->country == 'VN' ? 'selected' : '' }}>Vietnam</option>
                                                            <option value="YE" {{ $patient->country == 'YE' ? 'selected' : '' }}>Yemen</option>
                                                            <option value="ZM" {{ $patient->country == 'ZM' ? 'selected' : '' }}>Zambia</option>
                                                            <option value="ZW" {{ $patient->country == 'ZW' ? 'selected' : '' }}>Zimbabwe</option>
                                        </select>
                                    </div>
                                    <div class="mb-3">
                                        <label for="state" class="form-label">{{ __('master.state') }}</label>    
                                        <input type="text" class="form-control" id="state" name="state" required value="{{ old('state', $patient->state) }}">
                                    </div>
                                    <div class="mb-3">
                                        <label for="zip" class="form-label">{{ __('master.zip') }}</label>    
                                        <input type="text" class="form-control" id="zip" name="zip" required value="{{ old('zip', $patient->zip) }}">
                                    </div>
                                    <div class="mb-3 text-end">
                                        <button type="submit" class="btn btn-primary">{{ __('master.update') }}</button>
                                    </div>
                                </div>
                            </div>
                        </form> 







                    </div>
                </div>
            </div>
        </div>
    </div>
    @push('scripts')
    <script src="{{ asset('assets/vendor/libs/select2/select2.js') }}"></script>
    <script src="{{ asset('assets/vendor/libs/flatpickr/flatpickr.js') }}"></script>
    <script>
        flatpickr("#flatpickr-date", {
            dateFormat: "Y-m-d",
        });
        $(document).ready(function() {
            $('.select2').select2();
        });
    </script>
    @endpush
</x-app-layout>
