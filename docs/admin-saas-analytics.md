# Admin SaaS analytics definitions

The Admin Dashboard reports KeyFleet's own SaaS business. It intentionally does not
aggregate tenant rental bookings, vehicle revenue, rental collections, or fleet data.

## Revenue

- **Gross billings**: subscriptions.total_due for rows with paid_at in the selected period.
- **Net SaaS revenue**: subscriptions.net_amount minus subscriptions.refund_amount.
- **Gateway fees**: recorded subscriptions.paymongo_fee.
- **Discounts**: recorded subscriptions.discount_amount.
- **Plan MRR**: active paid plan_prices.price, normalized by billing-cycle months.
- **ARR run rate**: plan MRR multiplied by 12.
- Add-on receipts remain part of paid subscription revenue when included in the source
  subscription payment, but are not included in plan MRR because add-on price records do
  not currently provide a sufficiently consistent recurring-price definition.

## Subscriber lifecycle

- **New paid subscription**: a paid subscription with no earlier paid subscription for
  the same company.
- **Renewal**: a paid subscription with an earlier paid subscription for the same company.
- **Churned**: a subscription ending in the period with no later subscription for the
  same company.
- **Expiring soon**: an active paid subscription ending within 30 days.

## Agent channel

- **Attributed revenue**: agent_commissions.commissionable_amount.
- **Commission expense**: non-reversed commission amounts earned in the selected period.
- Commission pipeline states use the model's real values: pending, payable, paid,
  and reversed.
- Payout totals come from agent_payouts; they are not inferred from commission status.

Named calendar periods compare with the prior equivalent calendar period (for example,
this month versus last month and this year versus last year). Rolling and custom ranges
compare with the immediately preceding range with the same inclusive number of days.
