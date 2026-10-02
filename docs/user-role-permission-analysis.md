# User, Role, Permission, and Attribution Analysis

## Existing architecture

1. Company is the existing Filament tenant, identified by companies.id and routed by companies.slug.
2. Membership was stored in company_user as many-to-many, while application code selected the first company. users.company_id is now authoritative; the pivot remains temporarily for rollback compatibility.
3. subscriptions belongs to a company and resolves its current plan through plan_prices. Plans previously had only car_limit; user_limit is added.
4. Authorization previously consisted of users.is_admin, Filament tenant membership, tenant scopes, and scattered visibility checks. No permission package or policy layer was installed.
5. ApplyTenantScopes covers the principal company-owned models in Filament. Direct API and document lookups needed explicit tenant checks.

Inactive users do not consume seats. This preserves creator/updater history while allowing replacement staff accounts.

## Module and permission inventory

| Module | Existing actions | Permissions |
| --- | --- | --- |
| Dashboard | operational/financial widgets | dashboard.view |
| Bookings | CRUD, cancel, import/export, print, payments, inspections | bookings.view/create/update/delete/cancel/import/export/print/payments |
| Quotations | CRUD, import/export, invoice, convert | quotations.view/create/update/delete/import/export/print/convert |
| Reservations | view, approve/convert, decline/cancel | reservations.view/update/approve/cancel |
| Customers | CRUD, export, QR, requirements | customers.view/create/update/delete/export/requirements |
| Fleet | cars and documents CRUD | cars.*, car_documents.* |
| Finance | expenses CRUD/import/export and fund accounts | expenses.*, fund_types.* |
| Partners | CRUD and portal-token management | partners.view/create/update/delete/tokens |
| Sources | CRUD | sources.view/create/update/delete |
| Inspections | checklist CRUD, inspections, print | checklist_items.*, inspections.view/manage/print |
| Calendar | booking calendar | calendar.view |
| Reports | utilization, revenue, income flow, commissions | reports.view, reports.financial |
| Settings | profile, website, contract builder | settings.view/update, contracts.manage |
| Subscription | view/change plan | subscription.view/manage |
| Access control | users and roles | users.view/create/update/deactivate, roles.view/create/update/delete |

The protected Owner role receives the full catalog. Suggested custom roles are Manager, Booking Staff, Finance Staff, Fleet Staff, and Viewer. They are not auto-created because each tenant should choose its boundaries.

## Attribution scope

Direct tenant/business tables:

- companies, bookings, cars, customers, expenses, fund_types
- booking_payments, car_documents, partners, reservations, sources
- checklist_items, contracts, company_websites

Child tables whose tenant is derived through a parent:

- booking_inspections, inspection_items, customer_requirements, car_images

Excluded tables:

- Laravel infrastructure: migrations, cache, jobs, sessions, password resets, tokens
- Filament import/export infrastructure and notifications
- Global reference data: plans/prices, car types, checklist groups, requirement types, invoice templates, premium features, add-ons, vouchers, testimonials, and blog tables
- System/billing links: subscriptions, add-on subscriptions, voucher usage, referrals

These exclusions are platform-managed or system-generated rather than ordinary tenant-authored business records.

## Backfill and database changes

- Add nullable users.company_id, users.role_id, status, and last-login fields.
- Create tenant roles, global permissions, and role-permission assignments.
- Resolve membership from company_user. A user linked to multiple companies is logged and left for manual remediation.
- Create one protected Owner per company and assign the original valid member. Unexpected extra legacy members receive a full-access compatibility role to preserve existing access.
- Backfill every legacy business row to the Owner of that row's company. Child records resolve through their parent.
- Malformed/unowned records stay nullable and are logged with table/count details.
- Restrictive user foreign keys preserve historical attribution.
- Limits: DriveLite 1, RoadRunner 3, DrivePro 5, FleetMaster 10. Null means custom/unlimited.

## Affected areas and compatibility risks

Affected areas are migrations, models, reusable attribution, authentication, registration, tenant middleware, Filament resources/pages/widgets, APIs, document routes, and tests.

The main risks are ambiguous pivot membership, records missing tenant data, and direct-ID endpoints. The migration never guesses ambiguous data, retains the old pivot during transition, permits null attribution for unauthenticated system work, blocks removal of the last Owner, and treats permission checks separately from tenant ownership.

## Implementation sequence

1. Apply staged schema and legacy backfill.
2. Enable single-company/single-role models, limits, Owner protection, and attribution.
3. Enforce permissions and ownership in Filament, APIs, exports, reports, and documents.
4. Enable tenant User and Role management.
5. Run isolation, permission, seat, Owner, attribution, and backfill tests.
