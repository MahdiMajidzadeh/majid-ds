{{--
    The Latin -> Persian digit map, once per page. Five views used to inline
    their own copy of the same replace(), which is the JS twin of
    Persian::digits(). @once keys on this block, so however many components
    pull it in, it is emitted a single time.
--}}
@once('mds-digits')
<script>
window.mds = window.mds || {}

// Display-only: machine values stay Latin in the hidden inputs.
window.mds.digits = (value, fa = true) => {
    const s = String(value)

    return fa ? s.replace(/[0-9]/g, d => '۰۱۲۳۴۵۶۷۸۹'[+d]) : s
}

// The inverse, for reading a Persian- or Arabic-typed value back.
window.mds.latinDigits = (value) => String(value)
    .replace(/[۰-۹]/g, d => '۰۱۲۳۴۵۶۷۸۹'.indexOf(d))
    .replace(/[٠-٩]/g, d => '٠١٢٣٤٥٦٧٨٩'.indexOf(d))
</script>
@endonce
