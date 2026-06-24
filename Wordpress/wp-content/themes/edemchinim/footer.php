<?php
$site_phone = edemchinim_get_option_field('shina_phone', '+7 (000) 000-00-00');
$site_phone_href = edemchinim_phone_href($site_phone);
?>

<nav class="bottom-nav" aria-label="Основные разделы">
	<button
		class="bottom-nav__item bottom-nav__item--active"
		type="button"
		data-screen-target="order"
		aria-current="page">
		<svg
			class="icon-circle icon-circle--small"
			xmlns="http://www.w3.org/2000/svg"
			viewBox="0 0 24 24">
			<path
				fill="currentColor"
				d="M8.308 17.692h.884v-2h2v-.884h-2v-2h-.884v2h-2v.884h2zm5-.75h4.384v-.884h-4.384zm0-2.5h4.384v-.884h-4.384zm.792-3.953l1.4-1.4l1.4 1.4l.627-.627l-1.4-1.412l1.4-1.4l-.627-.627l-1.4 1.4l-1.4-1.4l-.627.627l1.4 1.4l-1.4 1.412zM6.558 8.892h4.384v-.884H6.558zM5.616 20q-.691 0-1.153-.462T4 18.384V5.616q0-.691.463-1.153T5.616 4h12.769q.69 0 1.153.463T20 5.616v12.769q0 .69-.462 1.153T18.384 20zm0-1h12.769q.23 0 .423-.192t.192-.424V5.616q0-.231-.192-.424T18.384 5H5.616q-.231 0-.424.192T5 5.616v12.769q0 .23.192.423t.423.192M5 5v14z"></path>
		</svg><span>Заказать</span></button><button
		class="bottom-nav__item"
		type="button"
		data-screen-target="reviews">
		<svg
			class="icon-circle icon-circle--small"
			xmlns="http://www.w3.org/2000/svg"
			viewBox="0 0 24 24">
			<path
				fill="currentColor"
				d="m12.34 12.192l1.93-1.163l1.928 1.163l-.523-2.196l1.712-1.475l-2.24-.186l-.878-2.066l-.877 2.066l-2.24.186l1.712 1.475zm4.514 6.27h1.485q-.002.361-.245.617t-.61.317L6.182 20.817q-.671.087-1.2-.32q-.527-.407-.608-1.078l-1.23-9.713q-.08-.672.333-1.216t1.085-.606l.977-.073v1l-.823.068q-.27.019-.424.221t-.115.471l1.196 9.713q.039.27.23.424q.193.153.463.115zm-7.7-2q-.69 0-1.153-.463t-.462-1.153V4.616q0-.691.462-1.153T9.154 3h10.23q.691 0 1.153.463T21 4.616v10.23q0 .69-.463 1.153t-1.153.463zm0-1h10.23q.27 0 .443-.173t.173-.443V4.616q0-.27-.173-.443T19.385 4H9.154q-.27 0-.442.173q-.173.173-.173.443v10.23q0 .27.173.443t.442.173M5.45 19.9"></path>
	</svg><span>Отзывы</span></button><a
		class="bottom-nav__call"
		href="<?php echo esc_url($site_phone_href); ?>"
		aria-label="<?php echo esc_attr(sprintf('Позвонить: %s', $site_phone)); ?>"><svg
			class="icon-circle"
			xmlns="http://www.w3.org/2000/svg"
			viewBox="0 0 24 24">
			<path
				fill="currentColor"
				d="M4.05 21q-.45 0-.75-.3t-.3-.75V15.9q0-.325.225-.587t.575-.363l3.45-.7q.35-.05.713.063t.587.337L10.9 17q.95-.55 1.8-1.213t1.625-1.437q.825-.8 1.513-1.662t1.187-1.788L14.6 8.45q-.2-.2-.275-.475T14.3 7.3l.65-3.5q.05-.325.325-.562T15.9 3h4.05q.45 0 .75.3t.3.75q0 3.125-1.362 6.175t-3.863 5.55t-5.55 3.863T4.05 21"></path>
		</svg></a><button
		class="bottom-nav__item"
		type="button"
		data-screen-target="photo">
		<svg
			class="icon-circle icon-circle--small"
			xmlns="http://www.w3.org/2000/svg"
			viewBox="0 0 24 24">
			<path
				fill="currentColor"
				d="M4.116 18q-.667 0-1.141-.475t-.475-1.14v-8.77q0-.666.475-1.14T4.115 6h8.77q.666 0 1.14.475t.475 1.14v8.77q0 .666-.475 1.14t-1.14.475zm13.201-7q-.357 0-.587-.23t-.23-.587V6.817q0-.357.23-.587t.587-.23h3.366q.357 0 .587.23t.23.587v3.366q0 .358-.23.587t-.587.23zm.183-1h3V7h-3zM4.116 17h8.769q.269 0 .442-.173t.173-.442v-8.77q0-.269-.173-.442T12.885 7h-8.77q-.269 0-.442.173t-.173.443v8.769q0 .269.173.442t.443.173m.576-2.096h7.616l-2.433-3.25L8 14.154l-1.375-1.825zM17.317 18q-.357 0-.587-.23t-.23-.587v-3.366q0-.357.23-.587t.587-.23h3.366q.357 0 .587.23t.23.587v3.366q0 .358-.23.587t-.587.23zm.183-1h3v-3h-3zm-14 0V7zm14-7V7zm0 7v-3z"></path>
		</svg><span>Фото</span></button><button
		class="bottom-nav__item"
		type="button"
		data-screen-target="faq">
		<svg
			class="icon-circle icon-circle--small"
			xmlns="http://www.w3.org/2000/svg"
			viewBox="0 0 24 24">
			<path
				fill="currentColor"
				d="M12 20.962q-3.014-.895-5.007-3.651T5 11.1V5.692l7-2.615l7 2.615V11.1q0 3.454-1.993 6.21T12 20.963m0-1.062q2.6-.825 4.3-3.3t1.7-5.5V6.375l-6-2.23l-6 2.23V11.1q0 3.025 1.7 5.5t4.3 3.3m.43-3.559q.197-.197.197-.468t-.197-.468t-.468-.197t-.469.197t-.197.468t.197.468q.198.198.469.198t.468-.198m-.905-2.58h.885q.019-.255.057-.511t.164-.467q.169-.273.371-.501t.42-.44q.424-.444.756-.94t.332-1.16q0-.97-.738-1.607q-.737-.635-1.733-.635q-.847 0-1.538.45T9.496 9.177l.812.339q.227-.52.697-.832t1.034-.311q.627 0 1.106.383t.48.998q0 .483-.291.893q-.292.41-.636.75q-.28.255-.523.533q-.242.278-.417.614q-.137.292-.185.593t-.048.624"></path>
		</svg><span>FAQ</span>
	</button>
</nav>
</div>

<?php wp_footer(); ?>

</body>

</html>
