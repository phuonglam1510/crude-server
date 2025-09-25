
/**
 * Function to return sum from 1 to n 
 * Complexity: O(n)
 * Efficiency: Simple to understand, easy to maintain or enhance
 * 
 * @param n 
 * @returns number
 */

function sum_to_n_a(n: number): number {

    let sum = 0;
    for (let i = 1; i <= n; i++) {
        sum += i;
    }
    return sum;
}


/**
 * Function to return sum from 1 to n 
 * Complexity: O(n)
 * Efficiency: Need medium understanding of rescursive implementation. Can control the logics and enhance if needed
 * 
 * @param n 
 * @returns number
 */


function sum_to_n_b(n: number): number {
    if (n <= 0) {
        return 0;
    }
    return n + sum_to_n_b(n - 1);
}


/**
 * Function to return sum from 1 to n 
 * Complexity: O(1)
 * Efficiency: This is a well-known formula and proved by famous mathematician. Limitation on modification for example if we want to ouput 2+3+...+n
 * 
 * @param n 
 * @returns number
 */



function sum_to_n_c(n: number): number {
    return (n * (n + 1)) / 2;
}



