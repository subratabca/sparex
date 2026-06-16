All three verified against the live API in your app's exact geocoding format — each lands cleanly in its target band:

## ✅ Verified addresses (all England → Greater London → Barnet)

**Customer shipping (delivery) address** — the fixed point
| Field | Value |
|---|---|
| Country / County / City | England / Greater London / Barnet |
| Address | **12 High Street** |
| Postcode | **EN5 5XQ** |
.........................................................................

**Client / Restaurant 1** — band 1–2 km
| Field | Value |
|---|---|
| Address | 10 Wellhouse Lane |
| Postcode | EN5 3DJ |
| ➜ Distance to shipping | **1.01 km** → tier `inside_city_2km` |
............................................................................

**Client / Restaurant 2** — band 2–5 km
| Field | Value |
|---|---|
| Address | 1250 High Road |
| Postcode | N20 9BH |
| ➜ Distance to shipping | **3.97 km** → tier `inside_city_5km` |
.................................................................................

**Client / Restaurant 3** — band 5–10 km
| Field | Value |
|---|---|
| Address | 12 Ballards Lane |
| Postcode | N12 0DR |
| ➜ Distance to shipping | **7.62 km** → tier `inside_city_10km` |
.....................................................................................
### Notes
- Distances are **driving** distance from the Google Distance Matrix API (exactly what your checkout uses), so when entered into the app they'll compute these same values and fall into three **distinct delivery-charge tiers** (≤2 km, ≤5 km, ≤10 km).
- For **City**, select **Barnet** for all of them — Whetstone (N20) and Finchley (N12) are localities within the **London Borough of Barnet**, and since the app geocodes from `address1 + postcode` (not the city field), this won't affect the distance.

## Want me to insert these into the DB?
I can, but I'll need a couple of details since these map to **existing client and shipping records** (and have FKs):
- Which **3 client user IDs** should get addresses 1/2/3 (or should I find your 3 client accounts)?
- Should I update an existing **shipping address row** (which `meal_order_id`) or your customer's profile address with the `12 High Street, EN5 5XQ` point?

Tell me the records and I'll update them (and geocode/populate their `latitude`/`longitude` at the same time, so the maps + distance work end-to-end).