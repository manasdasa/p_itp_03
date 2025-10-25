(function () {
  function checkDiscount() {
    const minute = new Date().getMinutes();
    const isOdd = minute % 2 === 1;
    const msg = isOdd
      ? `🎉 Discount available! Minute ${minute} is odd — show this popup at checkout.`
      : `😅 No discount right now. Minute ${minute} is even — try again in a minute.`;
    alert(msg);
  }

  document.addEventListener('DOMContentLoaded', function () {
    const btn = document.getElementById('discountBtn');
    if (btn) btn.addEventListener('click', checkDiscount);
  });
})();
