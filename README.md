# Billie Pay After Delivery
## Introduction
The Pay After Delivery product enables merchants to accept invoice payments from business customers (hereafter referred to as debtors). Billie takes over the reconciliation of payments, dunning processes and credit risk.

Based upon the contractual agreement with Billie, debtors have either 14, 30, 45, 60, 90 or 120 days to pay their invoices. However, the merchant receives funds from Billie immediately when the product is shipped, or the service obligation is fulfilled.

If debtors do not settle their outstanding invoices on time, Billie also sends reminders to these debtors on the merchant’s behalf. Pay After Delivery is a white label solution so these letters are sent by Billie in the merchant’s own branding, and the relationship between Billie and the merchant is not disclosed. If a debtor declares bankruptcy, Billie fully covers this default for the merchant.

## Usage of virtual IBANs
A virtual IBAN is generated for each new invoice. These IBANs are unique to a debtor and must be the only payment details displayed on the invoice which the debtor receives. Virtual IBANs are made available to the merchant after the successful creation of an order. They are essential to ensure Billie is able to efficiently and accurately match payments to invoices. Virtual IBANs ensure fast payout times and reliable payment reconciliations and help avoid unwarranted dunning.

## Requirements
- PHP 7.2 or higher
- Magento 2.3 or higher

## Magento Installation

Go to your installation directory of Magento 2 and perform the following commands
```bash
composer require billie/magento2-payment-module
```

Run the following commands to update the Magento database and to generate the interceptors:
```bash
bin/magento setup:upgrade
```

If your system is in the `production` mode you have to run the following commands:
```bash
bin/magento setup:di:compile
bin/magento setup:static-content:deploy # add your specific options which meets your environment
```

## Magento Configuration

You have to set up the API credentials for the payment method.

1. Please navigate to `Stores > Configuration > Sales > Payment Methods > Billie Rechnungskauf`.
2. In this configuration, you can enter the credentials and enable the payment method.
3. Clear/flush Magento cache

## Contact
Billie GmbH<br/>
Charlottenstraße 4<br/>
10969 Berlin<br/>
