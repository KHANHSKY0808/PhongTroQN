<?php

class Validator
{
    private array $data;
    private array $errors = [];

    public function __construct(array $data = [])
    {
        $this->data = $data;
    }

    public static function make(array $data, array $rules, array $customMessages = []): self
    {
        $validator = new self($data);
        $validator->validate($rules, $customMessages);
        return $validator;
    }

    public function validate(array $rules, array $customMessages = []): bool
    {
        $this->errors = [];

        foreach ($rules as $field => $fieldRules) {
            $value = $this->data[$field] ?? null;
            $ruleList = is_array($fieldRules) ? $fieldRules : explode('|', $fieldRules);

            $isRequired = in_array('required', $ruleList, true);

            // Nếu không bắt buộc và giá trị rỗng/null thì bỏ qua các rule khác
            if (!$isRequired && ($value === null || $value === '')) {
                continue;
            }

            foreach ($ruleList as $ruleItem) {
                $params = [];
                if (strpos($ruleItem, ':') !== false) {
                    [$ruleName, $paramStr] = explode(':', $ruleItem, 2);
                    $params = explode(',', $paramStr);
                } else {
                    $ruleName = $ruleItem;
                }

                $ruleName = trim($ruleName);

                if (!$this->checkRule($field, $value, $ruleName, $params)) {
                    $msgKey = "{$field}.{$ruleName}";
                    $message = $customMessages[$msgKey] 
                        ?? $customMessages[$ruleName] 
                        ?? $this->defaultMessage($field, $ruleName, $params);
                    $this->errors[$field][] = $message;
                    break;
                }
            }
        }

        return empty($this->errors);
    }

    private function checkRule(string $field, mixed $value, string $rule, array $params): bool
    {
        switch ($rule) {
            case 'required':
                if (is_null($value)) return false;
                if (is_string($value) && trim($value) === '') return false;
                if (is_array($value) && empty($value)) return false;
                return true;

            case 'numeric':
                return is_numeric($value);

            case 'integer':
                return filter_var($value, FILTER_VALIDATE_INT) !== false;

            case 'min':
                if (!is_numeric($value)) return false;
                $min = (float)($params[0] ?? 0);
                return (float)$value >= $min;

            case 'max':
                if (!is_numeric($value)) return false;
                $max = (float)($params[0] ?? 0);
                return (float)$value <= $max;

            case 'min_len':
                $minLen = (int)($params[0] ?? 0);
                return mb_strlen((string)$value, 'UTF-8') >= $minLen;

            case 'max_len':
                $maxLen = (int)($params[0] ?? 0);
                return mb_strlen((string)$value, 'UTF-8') <= $maxLen;

            case 'email':
                return filter_var($value, FILTER_VALIDATE_EMAIL) !== false;

            case 'phone':
                // Số điện thoại Việt Nam 10 chữ số (03, 05, 07, 08, 09)
                return (bool)preg_match('/^(0[3|5|7|8|9])[0-9]{8}$/', (string)$value);

            case 'latitude':
                if ($value === null || $value === '') return true;
                if (!is_numeric($value)) return false;
                $lat = (float)$value;
                return $lat >= -90.0 && $lat <= 90.0;

            case 'longitude':
                if ($value === null || $value === '') return true;
                if (!is_numeric($value)) return false;
                $lng = (float)$value;
                return $lng >= -180.0 && $lng <= 180.0;

            case 'video_url':
                if ($value === null || $value === '') return true;
                return self::isValidVideoUrl((string)$value);

            default:
                return true;
        }
    }

    public static function isValidVideoUrl(string $url): bool
    {
        $parsed = parse_url(trim($url));
        if (!$parsed || empty($parsed['host']) || empty($parsed['scheme'])) {
            return false;
        }

        if (!in_array(strtolower($parsed['scheme']), ['http', 'https'], true)) {
            return false;
        }

        $host = strtolower($parsed['host']);
        if (str_starts_with($host, 'www.')) {
            $host = substr($host, 4);
        }

        $allowedHosts = [
            'youtube.com',
            'm.youtube.com',
            'youtu.be',
            'tiktok.com',
            'vm.tiktok.com'
        ];

        return in_array($host, $allowedHosts, true);
    }

    private function defaultMessage(string $field, string $rule, array $params): string
    {
        $names = [
            'title'      => 'Tiêu đề',
            'TieuDe'     => 'Tiêu đề',
            'diachi'     => 'Địa chỉ',
            'DiaChi'     => 'Địa chỉ',
            'gia'        => 'Giá phòng',
            'GiaPhong'   => 'Giá phòng',
            'dientich'   => 'Diện tích',
            'DienTich'   => 'Diện tích',
            'mota'       => 'Mô tả',
            'MoTa'       => 'Mô tả',
            'latitude'   => 'Vĩ độ (Latitude)',
            'longitude'  => 'Kinh độ (Longitude)',
            'video_link' => 'Đường dẫn video',
            'phone'      => 'Số điện thoại',
            'email'      => 'Email',
            'hoten'      => 'Họ tên',
            'sdt'        => 'Số điện thoại'
        ];

        $name = $names[$field] ?? $field;

        return match ($rule) {
            'required'  => "Trường '{$name}' không được để trống.",
            'numeric'   => "Trường '{$name}' phải là một số hợp lệ.",
            'integer'   => "Trường '{$name}' phải là số nguyên.",
            'min'       => "Trường '{$name}' phải có giá trị tối thiểu là " . number_format((float)($params[0] ?? 0)) . ".",
            'max'       => "Trường '{$name}' không được vượt quá " . number_format((float)($params[0] ?? 0)) . ".",
            'min_len'   => "Trường '{$name}' phải có ít nhất {$params[0]} ký tự.",
            'max_len'   => "Trường '{$name}' không được vượt quá {$params[0]} ký tự.",
            'email'     => "Trường '{$name}' phải là địa chỉ email hợp lệ.",
            'phone'     => "Trường '{$name}' phải là số điện thoại Việt Nam hợp lệ (10 chữ số).",
            'latitude'  => "Vĩ độ (Latitude) phải nằm trong khoảng -90 đến 90.",
            'longitude' => "Kinh độ (Longitude) phải nằm trong khoảng -180 đến 180.",
            'video_url' => "Đường dẫn video chỉ chấp nhận link từ YouTube (youtube.com, youtu.be) hoặc TikTok (tiktok.com).",
            default     => "Trường '{$name}' không hợp lệ."
        };
    }

    public function passes(): bool
    {
        return empty($this->errors);
    }

    public function fails(): bool
    {
        return !empty($this->errors);
    }

    public function errors(): array
    {
        return $this->errors;
    }

    public function firstError(): ?string
    {
        foreach ($this->errors as $fieldErrors) {
            if (!empty($fieldErrors)) {
                return $fieldErrors[0];
            }
        }
        return null;
    }

    public function allErrors(): array
    {
        $all = [];
        foreach ($this->errors as $fieldErrors) {
            foreach ($fieldErrors as $msg) {
                $all[] = $msg;
            }
        }
        return $all;
    }
}
