# G2A API Code Buyer
This is a simple Symfony console application designed to automate the process of purchasing product keys from the G2A marketplace via its API. The script handles creating orders, processing payments, and retrieving the purchased keys.

This application is currently configured to use the G2A Sandbox environment.

## How It Works
The application provides a single console command, buy, which performs the following actions in sequence:

- Creates Orders: It initiates one or more orders for a specified product ID.
- Pays for Orders: It processes the payment for all the newly created orders.
- Retrieves Keys: It fetches the product keys from the paid orders.
- Outputs Keys: The retrieved keys are printed to the console output, one per line.

Error handling is implemented for both application-level issues and API communication errors. Any failures during the process will be reported in the console.

## Getting Started
### Prerequisites
PHP 8.3 or higher

### Composer

#### Installation
1. Clone the repository:
```
git clone https://github.com/bartoszjuszczyk/g2a-api-buy-codes.git
cd g2a-api-buy-codes
```
2. Install the required dependencies using Composer:
```
composer install
```
This will install symfony/console and the G2A API client library, among other dependencies.

## Usage
The application is executed through the cmd.php script. The main command is buy, which accepts several options to specify what you want to purchase.

### Command
```
php cmd.php buy [options]
```
### Options
| Option      | Shortcut | Description                               | Required   | Default |
|-------------|----------|-------------------------------------------|------------|---------|
| --product   | -p       | The ID of the product to buy.             | yes        | ------- |
| --inventory | -i       | The quantity of the product to buy.       | no         | g2a     |
| --qty       | -------- | The G2A inventory to use.                 | no         | 1       |


### Example
To buy 5 keys for the product with ID 12345:

```
php cmd.php buy --product=12345 --qty=5
```
Or using the shortcut for the product ID:
```
php cmd.php buy -p 12345 --qty=5
```
If the purchase is successful, the command will output the five product keys, each on a new line.
```
FAKEKEY-ABCDE-11111
FAKEKEY-FGHIJ-22222
FAKEKEY-KLMNO-33333
FAKEKEY-PQRST-44444
FAKEKEY-UVWXY-55555
```