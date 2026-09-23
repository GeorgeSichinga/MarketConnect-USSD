-- Market Connect - adds a unit (KG / 50kg Bag) to market prices.
-- Run against the `market_connect` database, same way as before.

ALTER TABLE market_info
  ADD COLUMN Price_Unit ENUM('KG', '50kg Bag') NOT NULL DEFAULT 'KG' AFTER Price;

-- Note: if you already added test prices before this ran, they'll now show
-- as "KG" by default since that's a guess, not a stored fact - double check
-- (or just re-add) any prices you entered earlier if they were actually
-- meant to be per-bag.
