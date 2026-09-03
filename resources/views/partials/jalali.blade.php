{{--
    The Jalali calendar in JavaScript, once per page — the JS twin of
    MajidDs\Support\Jalali. The arithmetic below is a line-for-line port of
    fromGregorian(), toGregorian() and isLeapYear() (PHP's intdiv() is
    Math.trunc() here; both truncate toward zero, and `%` follows the
    dividend's sign in both languages), and the month/weekday names are
    emitted from the PHP constants with @js, so the two sides cannot drift.

    API (window.mds.jalali):
      toJalali(gy, gm, gd)     -> [jy, jm, jd]
      toGregorian(jy, jm, jd)  -> [gy, gm, gd]
      isLeapYear(jy)           -> bool (Esfand has 30 days)
      daysInMonth(jy, jm)      -> 31 | 30 | 29
      monthNames.fa / .en      -> 12 names, index 0 = Farvardin
      weekdayNames.fa / .en    -> 7 names, index 0 = Sunday (PHP's `w`)
      toDays(gy, gm, gd)       -> days since 1970-01-01 (Gregorian, no Date object, no timezone)
      fromDays(n)              -> [gy, gm, gd]
    The last two exist so a grid can move by ±n days without touching Date,
    whose local-time arithmetic skips or repeats a day across DST changes.
--}}
@once('mds-jalali')
<script @mdsNonce>
window.mds = window.mds || {}

window.mds.jalali = {
    monthNames: {
        fa: @js(array_values(\MajidDs\Support\Jalali::MONTHS)),
        en: @js(array_values(\MajidDs\Support\Jalali::MONTHS_LATIN)),
    },

    weekdayNames: {
        fa: @js(array_values(\MajidDs\Support\Jalali::WEEKDAYS)),
        en: @js(array_values(\MajidDs\Support\Jalali::WEEKDAYS_LATIN)),
    },

    // Jalali::isLeapYear — the same 33-year-cycle count the converters use.
    isLeapYear(jy) {
        const leaps = (y) => Math.trunc(y / 33) * 8 + Math.trunc(((y % 33) + 3) / 4)

        return leaps(jy + 1595 + 1) - leaps(jy + 1595) === 1
    },

    // Farvardin–Shahrivar 31, Mehr–Bahman 30, Esfand 29 or 30.
    daysInMonth(jy, jm) {
        if (jm <= 6) return 31
        if (jm <= 11) return 30

        return this.isLeapYear(jy) ? 30 : 29
    },

    // Jalali::fromGregorian
    toJalali(gy, gm, gd) {
        const daysInMonths = [0, 31, 59, 90, 120, 151, 181, 212, 243, 273, 304, 334]

        const gy2 = gm > 2 ? gy + 1 : gy

        let days = 355666 + (365 * gy)
            + Math.trunc((gy2 + 3) / 4)
            - Math.trunc((gy2 + 99) / 100)
            + Math.trunc((gy2 + 399) / 400)
            + gd + daysInMonths[gm - 1]

        let jy = -1595 + (33 * Math.trunc(days / 12053))
        days %= 12053

        jy += 4 * Math.trunc(days / 1461)
        days %= 1461

        if (days > 365) {
            jy += Math.trunc((days - 1) / 365)
            days = (days - 1) % 365
        }

        let jm, jd

        if (days < 186) {
            jm = 1 + Math.trunc(days / 31)
            jd = 1 + (days % 31)
        } else {
            jm = 7 + Math.trunc((days - 186) / 30)
            jd = 1 + ((days - 186) % 30)
        }

        return [jy, jm, jd]
    },

    // Jalali::toGregorian
    toGregorian(jy, jm, jd) {
        jy += 1595

        let days = -355668 + (365 * jy)
            + (Math.trunc(jy / 33) * 8)
            + Math.trunc(((jy % 33) + 3) / 4)
            + jd
            + ((jm < 7) ? (jm - 1) * 31 : ((jm - 7) * 30) + 186)

        let gy = 400 * Math.trunc(days / 146097)
        days %= 146097

        if (days > 36524) {
            gy += 100 * Math.trunc(--days / 36524)
            days %= 36524

            if (days >= 365) {
                days++
            }
        }

        gy += 4 * Math.trunc(days / 1461)
        days %= 1461

        if (days > 365) {
            gy += Math.trunc((days - 1) / 365)
            days = (days - 1) % 365
        }

        let gd = days + 1

        const isLeap = ((gy % 4 === 0) && (gy % 100 !== 0)) || (gy % 400 === 0)

        const monthLengths = [0, 31, isLeap ? 29 : 28, 31, 30, 31, 30, 31, 31, 30, 31, 30, 31]

        let gm

        for (gm = 1; gm <= 12; gm++) {
            if (gd <= monthLengths[gm]) {
                break
            }

            gd -= monthLengths[gm]
        }

        return [gy, gm, gd]
    },

    // Proleptic Gregorian day serial (Howard Hinnant's days_from_civil):
    // 1970-01-01 is day 0, a Thursday. Pure integer arithmetic, so a
    // calendar can step ±n days without Date's local-time DST surprises.
    toDays(gy, gm, gd) {
        const y = gm <= 2 ? gy - 1 : gy
        const era = Math.floor(y / 400)
        const yoe = y - era * 400
        const doy = Math.trunc((153 * (gm + (gm > 2 ? -3 : 9)) + 2) / 5) + gd - 1
        const doe = yoe * 365 + Math.trunc(yoe / 4) - Math.trunc(yoe / 100) + doy

        return era * 146097 + doe - 719468
    },

    // The inverse (civil_from_days).
    fromDays(n) {
        const z = n + 719468
        const era = Math.floor(z / 146097)
        const doe = z - era * 146097
        const yoe = Math.trunc((doe - Math.trunc(doe / 1460) + Math.trunc(doe / 36524) - Math.trunc(doe / 146096)) / 365)
        const doy = doe - (365 * yoe + Math.trunc(yoe / 4) - Math.trunc(yoe / 100))
        const mp = Math.trunc((5 * doy + 2) / 153)
        const gd = doy - Math.trunc((153 * mp + 2) / 5) + 1
        const gm = mp + (mp < 10 ? 3 : -9)

        return [yoe + era * 400 + (gm <= 2 ? 1 : 0), gm, gd]
    },
}
</script>
@endonce
