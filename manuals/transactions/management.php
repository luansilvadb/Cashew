<h1>Transaction Management</h1>

<p>Cashew empowers you to track every cent with precision. Transaction management is the core of your financial journey, allowing you to categorize, tag, and monitor your spending and income across multiple accounts.</p>

<h2>Conceptual Overview</h2>
<p>In Cashew, a **Transaction** represents a single movement of money. It can be an **Expense** (money going out) or an **Income** (money coming in). Each transaction is tied to a **Category** and a **Wallet** (Account), ensuring that your reports and budgets stay accurate.</p>

<div class="ui-callout">
    Transactions are processed in real-time and synchronized across your devices if you have cloud sync enabled.
</div>

<h2>Procedural Steps</h2>

<h3>Adding a New Transaction</h3>
<ol>
    <li>On the **Home Screen**, tap the **Plus (+)** button.</li>
    <li>Enter the **Amount** of the transaction using the numeric keypad.</li>
    <li>Select the **Category** that best describes the transaction (e.g., Groceries, Salary).</li>
    <li>Ensure the correct **Wallet** is selected.</li>
    <li>(Optional) Add a **Note** or **Tags** for better searchability later.</li>
    <li>Tap **Save** to record the transaction.</li>
</ol>

<h3>Editing a Transaction</h3>
<ol>
    <li>Go to the **Transactions List** by tapping on the **Activity** tab or a specific category.</li>
    <li>Find the transaction you wish to modify.</li>
    <li>**Tap** the transaction to open the edit screen.</li>
    <li>Update the necessary fields (**Amount**, **Category**, **Date**, etc.).</li>
    <li>Tap **Update** to save your changes.</li>
</ol>

<h3>Deleting Transactions</h3>
<ol>
    <li>In the **Transactions List**, **Long-Press** on the transaction you want to delete.</li>
    <li>Select additional transactions if you wish to perform a bulk delete.</li>
    <li>Tap the **Trash** icon in the top toolbar.</li>
    <li>Confirm the deletion when prompted.</li>
</ol>

<h2>Technical Reference</h2>

<table>
    <thead>
        <tr>
            <th>Field</th>
            <th>Type</th>
            <th>Description</th>
        </tr>
    </thead>
    <tbody>
        <tr>
            <td>**Amount**</td>
            <td>Decimal</td>
            <td>The monetary value. Negative for expenses, positive for income.</td>
        </tr>
        <tr>
            <td>**Category**</td>
            <td>Selection</td>
            <td>The primary classification for the transaction.</td>
        </tr>
        <tr>
            <td>**Wallet**</td>
            <td>Selection</td>
            <td>The account from which the money is deducted or added.</td>
        </tr>
        <tr>
            <td>**Date**</td>
            <td>DateTime</td>
            <td>The timestamp when the transaction occurred. Defaults to now.</td>
        </tr>
    </tbody>
</table>

<h2>Edge Cases</h2>
<ul>
    <li>**Zero Amount**: Transactions with a zero amount are allowed but will not affect budget totals.</li>
    <li>**Future Date**: You can set a future date for transactions, which will mark them as **Upcoming**.</li>
    <li>**Currency Mismatch**: If you add a transaction to a wallet with a different currency than your primary one, Cashew will use the latest exchange rates for conversion in reports.</li>
</ul>
