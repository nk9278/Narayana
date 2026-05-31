<?php
/**
 * Reusable UI Components
 */
class UI {

    /**
     * Renders a premium button
     */
    public static function button($text, $type = 'submit', $extraClasses = '', $icon = '') {
        $baseClass = "bg-brand-wine text-brand-cultured py-3.5 px-8 rounded-full font-medium shadow-lg hover:bg-brand-burgundy hover:shadow-brand-wine/30 hover:-translate-y-0.5 transition-all duration-300 inline-flex items-center justify-center";
        $iconHtml = $icon ? "<i class='{$icon} mr-2'></i>" : "";
        return "<button type='{$type}' class='{$baseClass} {$extraClasses}'>{$iconHtml}{$text}</button>";
    }

    /**
     * Renders an alert message
     */
    public static function alert($message, $type = 'error') {
        $classes = [
            'error' => 'bg-red-50 text-red-600 border-red-100 fa-exclamation-circle',
            'success' => 'bg-green-50 text-green-700 border-green-200 fa-check-circle',
            'info' => 'bg-blue-50 text-blue-700 border-blue-200 fa-info-circle'
        ];

        $classInfo = $classes[$type] ?? $classes['info'];
        list($bg, $text, $border, $icon) = explode(' ', $classInfo);

        return "
        <div class='{$bg} {$text} px-4 py-3 rounded-lg text-sm mb-6 border {$border} flex items-center shadow-sm'>
            <i class='fas {$icon} mr-3 text-lg'></i> " . htmlspecialchars($message) . "
        </div>";
    }

    /**
     * Renders a standard input field
     */
    public static function input($name, $label, $type = 'text', $value = '', $required = true, $placeholder = '') {
        $reqAttr = $required ? 'required' : '';
        return "
        <div class='mb-4'>
            <label for='{$name}' class='block text-sm font-medium text-brand-wine mb-2'>" . htmlspecialchars($label) . "</label>
            <input type='{$type}' id='{$name}' name='{$name}' value='" . htmlspecialchars($value) . "' placeholder='" . htmlspecialchars($placeholder) . "' {$reqAttr}
                   class='w-full bg-brand-cultured/50 border border-brand-gold/30 rounded-xl py-3 px-4 text-brand-wine focus:bg-white focus:border-brand-gold focus:ring-1 focus:ring-brand-gold/50 outline-none transition-all duration-300'>
        </div>";
    }

    /**
     * Renders a loader HTML snippet
     */
    public static function loader() {
        return "
        <div class='flex justify-center items-center p-8'>
            <div class='animate-spin rounded-full h-12 w-12 border-t-2 border-b-2 border-brand-gold'></div>
        </div>";
    }

    /**
     * Renders a modal skeleton
     */
    public static function modal($id, $title, $content, $footer = '') {
        return "
        <div id='{$id}' class='fixed inset-0 z-[100] hidden overflow-y-auto' aria-labelledby='modal-title' role='dialog' aria-modal='true'>
            <div class='flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0'>
                <div class='fixed inset-0 bg-brand-burgundy/80 backdrop-blur-sm transition-opacity' aria-hidden='true'></div>
                <span class='hidden sm:inline-block sm:align-middle sm:h-screen' aria-hidden='true'>&#8203;</span>
                <div class='inline-block align-bottom bg-white rounded-2xl text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg w-full border border-brand-gold/30'>
                    <div class='bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4'>
                        <div class='sm:flex sm:items-start'>
                            <div class='mt-3 text-center sm:mt-0 sm:ml-4 sm:text-left w-full'>
                                <h3 class='text-xl leading-6 font-bold text-brand-wine' id='modal-title'>
                                    " . htmlspecialchars($title) . "
                                </h3>
                                <div class='mt-4'>
                                    {$content}
                                </div>
                            </div>
                        </div>
                    </div>
                    " . ($footer ? "<div class='bg-brand-cultured/30 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse border-t border-brand-gold/10'>{$footer}</div>" : "") . "
                </div>
            </div>
        </div>";
    }
}
?>