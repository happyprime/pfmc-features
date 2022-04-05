<?php
/**
 * Template for event `.ics` files.
 */

header( 'HTTP/1.0 200 OK', true, 200 );
header( 'Content-Type: text/calendar; charset=UTF-8' );
header( 'Content-Disposition: attachment; filename=invite.ics' );

$event_id        = intval( get_query_var( 'pfmc_sc_event_id' ) );
$event           = sugar_calendar_get_event_by_object( $event_id );
$location        = $event->location;
$format          = 'Ymd\THis';
$start_date      = $event->start_date( $format );
$end_date        = $event->end_date( $format );
$time_zone       = sugar_calendar_get_timezone();
$start_time_zone = ! empty( $event->start_tz ) ? $event->start_tz : $time_zone;
$end_time_zone   = ! empty( $event->end_tz ) ? $event->end_tz : $time_zone;

?>
BEGIN:VCALENDAR
VERSION:2.0
PRODID:-//<?php echo esc_html( get_bloginfo( 'name' ) ); ?>//NONSGML v1.0//EN
METHOD:PUBLISH
BEGIN:VEVENT
DTSTART;TZID=<?php echo esc_html( $start_time_zone ); ?>:<?php echo esc_html( $start_date ) . "\r\n"; ?>
DTEND;TZID=<?php echo esc_html( $end_time_zone ); ?>:<?php echo esc_html( $end_date ) . "\r\n"; ?>
SUMMARY:<?php echo esc_html( get_the_title( $event_id ) ) . "\r\n"; ?>
LOCATION:<?php echo esc_html( $location ) . "\r\n"; ?>
END:VEVENT
END:VCALENDAR
