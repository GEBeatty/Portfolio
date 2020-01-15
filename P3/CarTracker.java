import java.util.Scanner;
import java.util.Arrays;
import java.util.HashMap;
import java.util.InputMismatchException;
import java.io.*;

/*
 * Grace Beatty
 * CS 1501 - Project 3 - Car Tracker using Heaps
 * Last updated: 3/21/2019
 */

public class CarTracker
{
    public static void main(String[] args)
    {
        // Create first two simple heaps
        MinPriceHeap minPrice = new MinPriceHeap();
        MinMileHeap minMiles = new MinMileHeap();
        HashMap<String, CarNode> nodeMap = new HashMap<>(); // VIN, car
        HashMap<String, HeapHolder> mmMap = new HashMap<>();// Make/model miles

        try {
            // Read in the cars.txt file
            File file = new File("cars.txt");
            Scanner fileInput = new Scanner(file);
            fileInput.nextLine();
            String nextCar = "";
            // Fill in the trees
            while(fileInput.hasNext()){
                nextCar = fileInput.nextLine();
                String[] s = nextCar.split(":");
                int p = Integer.parseInt(s[3]);
                int m = Integer.parseInt(s[4]);
                // Create a new node
                CarNode aCar = new CarNode(s[0], s[1], s[2], p, m, s[5]);
                // Add this node to our heaps
                minPrice.addCar(aCar);
                minMiles.addCar(aCar);
                // Do we have that make and model yet?
                if(mmMap.containsKey(aCar.make+aCar.model)){
                    // If it does, add the car to those heaps
                    HeapHolder hold = mmMap.get(aCar.make+aCar.model);
                    hold.addToHeaps(aCar);
                } else {
                    // If it doesn't, we'll need to add a new HeapHolder
                    HeapHolder newHold = new HeapHolder();
                    mmMap.put(aCar.make+aCar.model, newHold);
                    newHold.addToHeaps(aCar);
                }
                nodeMap.put(aCar.vin, aCar);    // Put in index
            }
            fileInput.close();
        } catch (IOException e) {
            System.out.println("Sorry, the file doesn't exist\n");
        }
        
        // Main user menu
        Scanner userInput = new Scanner(System.in);
        String selection = "-1";
        String vin = "";
        String make = "";
        String model = "";
        int price = 0;
        int mileage = 0;
        String color = "";
        CarNode lowestPrice;
        CarNode lowestMileage;
        // Run the program until the user decides to stop
        boolean runProgram = true;
        while(runProgram)
        {
            if(nodeMap.isEmpty()){
                System.out.println("\nTHE SYSTEM IS EMPTY. PLEASE ADD A CAR.");
            }
            // Menu
            System.out.println("Please select an option:");
            System.out.println("1) Add a car\n"+
                            "2) Update a car\n"+
                            "3) Remove a car\n"+
                            "4) Retrieve lowest priced car\n"+
                            "5) Retrieve lowest mileage car\n"+
                            "6) Retreive lowest price of make/model\n"+
                            "7) Retrieve lowest mileage of make/model\n"+
                            "8) Exit");
            System.out.print(" > ");
            // Take user's input
            selection = userInput.nextLine();

            if(selection.equals("1")){ // Add a car
                // Get the car information
                System.out.print("Input VIN: ");
                vin = userInput.nextLine();
                while(vin.length() != 17){
                    System.out.print("Input appropriate 17-character VIN: ");
                    vin = userInput.nextLine();
                }
                vin = vin.toUpperCase();
                System.out.print("Input make: ");
                make = userInput.nextLine();
                System.out.print("Input model: ");
                model = userInput.nextLine();
                System.out.print("Input price: ");
                price = userInput.nextInt();
                System.out.print("Input mileage: ");
                mileage = userInput.nextInt();
                userInput.nextLine(); // Eat newline
                System.out.print("Input color: ");
                color = userInput.nextLine();

                // Create the carNode and add to heaps and index
                CarNode newCar = new CarNode(vin, make, model, price, mileage, color);
                minPrice.addCar(newCar);
                minMiles.addCar(newCar);
                // Do we have that make and model yet?
                if(mmMap.containsKey(newCar.make+newCar.model)){
                    // If it does, add the car to those heaps
                    HeapHolder hold = mmMap.get(newCar.make+newCar.model);
                    hold.addToHeaps(newCar);
                } else {
                    // If it doesn't, we'll need to add a new HeapHolder
                    HeapHolder newHold = new HeapHolder();
                    mmMap.put(newCar.make+newCar.model, newHold);
                    newHold.addToHeaps(newCar);
                }
                nodeMap.put(vin, newCar);
                // Display the addition
                System.out.println("\nAdded car:");
                newCar.display();
                System.out.println("");
            } else if (selection.equals("2") && !nodeMap.isEmpty()){// Update a car
                System.out.print("Please input the VIN: ");
                String updateVin = userInput.nextLine();
                CarNode uCar = nodeMap.get(updateVin);
                // If the vehicle wasn't found
                if(uCar == null){
                    System.out.println("Sorry, a vehicle with that VIN doesn't exist\n");
                } else {
                    // Display the vehicle
                    System.out.print("Selected vehicle:");
                    uCar.display();
                    System.out.println();
                    // Ask for the update
                    System.out.print("What would you like to update?" +
                                        "\n1) Price" +
                                        "\n2) Mileage" +
                                        "\n3) Color" +
                                        "\n > ");
                    selection = userInput.nextLine();
                    if(selection.equals("1")){ // Price
                        System.out.print("New price: ");
                        uCar.price = userInput.nextInt();
                        userInput.nextLine(); // Eat newline
                    } else if (selection.equals("2")){ // Mileage
                        System.out.print("New mileage: ");
                        uCar.mileage = userInput.nextInt();
                        userInput.nextLine(); // Eat newline
                    } else if (selection.equals("3")){ // Color
                        System.out.print("New color: ");
                        uCar.color = userInput.nextLine();
                    } else {
                        System.out.println("That's not a valid option");
                    }
                    // Update the car's position in the heaps
                    minPrice.updateCar(uCar);
                    minMiles.updateCar(uCar);
                    // We should have this model in a Heapholder
                    HeapHolder uHold = mmMap.get(uCar.make+uCar.model);
                    uHold.updateHeaps(uCar);
                    // Display updated car
                    System.out.print("Updated car:");
                    uCar.display();
                    System.out.println();
                }
            } else if (selection.equals("3") && !nodeMap.isEmpty()){ // Remove a car
                // Get the VIN and find the car
                System.out.print("Please input the VIN: ");
                String deleteVin = userInput.nextLine();
                CarNode dCar = nodeMap.get(deleteVin);
                // If the vehicle doesn't exist
                if(dCar == null){
                    System.out.println("Sorry, a vehicle with that VIN doesn't exist.\n");
                } else { // Display vehicle and ask for finality
                    System.out.print("\nSelected Vehicle:");
                    dCar.display();
                    System.out.print("Are you sure you want to delete? (y/n) : ");
                    String choice = userInput.nextLine();
                    if(choice.equalsIgnoreCase("y")){
                        // Update heaps and index
                        minPrice.deleteCar(dCar);
                        minMiles.deleteCar(dCar);
                        // We should have this model in a Heapholder
                        HeapHolder dHold = mmMap.get(dCar.make+dCar.model);
                        dHold.deleteFromHeaps(dCar);
                        nodeMap.remove(deleteVin);
                        System.out.println("Car deleted.\n");
                    }
                }
            } else if (selection.equals("4") && !nodeMap.isEmpty()){ // Get lowest priced car
                lowestPrice = minPrice.lowest();
                System.out.print("\nLowest Priced Car:");
                lowestPrice.display();
                System.out.println("");
            } else if (selection.equals("5") && !nodeMap.isEmpty()){ // Get lowest mileage car
                lowestMileage = minMiles.lowest();
                System.out.print("\nLowest Mileage Car:");
                lowestMileage.display();
                System.out.println("");
            } else if (selection.equals("6") && !nodeMap.isEmpty()){ // Get lowest price of make/model
                System.out.print("Input car make: ");
                String mpMake = userInput.nextLine();
                System.out.print("Input car model: ");
                String mpModel = userInput.nextLine();
                HeapHolder mpHold = mmMap.get(mpMake+mpModel);
                if(mpHold == null){
                    System.out.println("Sorry, there are no cars of that make and model in the system.");
                } else {
                    // Retrieve lowest from price heap
                    CarNode mpCar = mpHold.getLowestPrice();
                    System.out.print(mpMake +" "+ mpModel + " lowest price: ");
                    mpCar.display();
                }
            } else if (selection.equals("7")  && !nodeMap.isEmpty()){ // Get lowest mileage of make/model
                System.out.print("Input car make: ");
                String mmMake = userInput.nextLine();
                System.out.print("Input car model: ");
                String mmModel = userInput.nextLine();
                HeapHolder mmHold = mmMap.get(mmMake+mmModel);
                if(mmHold == null){
                    System.out.println("Sorry, there are no cars of that make and model in the system.");
                } else {
                    // Retrieve lowest from price heap
                    CarNode mmCar = mmHold.getLowestMile();
                    System.out.print(mmMake +" "+ mmModel + " lowest mileage: ");
                    mmCar.display();
                }
            } else if (selection.equals("8")){
                // End the program
                runProgram = false;
            } else {
                System.out.println("Please make a valid selection.");
            }
        }
        userInput.close();
        System.out.println("\nGoodbye!");
    }
}

/* Basic heap components class, to minimize code
 * Generally useful for basic functions, but more specialized
 * heaps are required because of CarNode's interesting index
 * structure
 */
class BaseHeap
{
    // An array to serve as the heap
    CarNode[] heap;
    int size;

    public BaseHeap()
    {
        // Set up the heap
        heap = new CarNode[20];
        // Set size to 0, keep track of available spots
        size = 0;
    }

    public CarNode lowest(){
        return heap[0];
    }

    public void resize()
    {
        // Double our heap size
        int newSize = heap.length * 2;
        CarNode[] temp = new CarNode[newSize];
        for(int i = 0; i < heap.length; i++){
            temp[i] = heap[i];
        }
        // Swap references
        heap = temp;
    }
}

/* A heap used to keep track of the minimum price of cars

   Quick notes: For index i starting at 0
     Parent node: floor[(i-1)/2]
     left child:  2i+1
     right child: 2i+2
 */
class MinPriceHeap extends BaseHeap
{
    /* Add a car to the heap by creating a new node, adding it
     * to the heap, and rearranging the nodes
     */
    public void addCar(CarNode car)
    {
        if(size == (heap.length-1)){ // Resize
            resize();
        }
        // Add the car to the first available spot
        heap[size] = car;
        car.setpIndex(size);
        // Arrange new car within the heap
        swim(heap[size], size);

        size += 1;  // Increase size
    }

    /* Update a car's position in the heap after making modifications to it
     * using the update option from the menu
     */
    public void updateCar(CarNode car)
    {
        // Try swimming
        swim(car, car.pIndex);
        // Try sinking
        sink(car, car.pIndex);
    }

    /* Delete a car from the heap
     */
    public void deleteCar(CarNode car)
    {
        // Determine the car's index.
        int dCarIndex = car.pIndex;
        // If it's the last or only car, just get rid of it
        if(dCarIndex == (size-1))
        {
            heap[size-1] = null;
            size -=1;   // Decrease size
        } else {
            // The final car is at heap[size-1]. Update the index
            heap[size-1].pIndex = dCarIndex;
            // Use the Node to replace the old car
            heap[dCarIndex] = heap[size-1];
            // Remove the previous reference
            heap[size-1] = null;
            size -=1;

            // Determine the updated place of the replacement car
            swim(heap[dCarIndex], dCarIndex);
            sink(heap[dCarIndex], dCarIndex);
        }
    }

    public void swim(CarNode car, int index)
    {
        // Have we reached the top?
        if(index == 0) return;
        // If the parent is lower priority, switch
        int parIndex = (int) Math.floor((index-1)/2);
        if(car.comparePrice(heap[parIndex])){
            // Swap the two cars
            car.pSwap(heap[parIndex]);
            CarNode temp = heap[parIndex];
            heap[parIndex] = car;
            heap[index] = temp;
            index = parIndex;  // Switch index
            // And recurse
            swim(car, index);
        }
        // Otherwise, we're at the proper priority
    }

    public void sink(CarNode car, int index)
    {
        // Indicies of the children
        int leftChild = (2*index) + 1;
        int rightChild = (2*index) + 2;
        // Are the left and right child within bounds?
        if(leftChild > size-1){
            // Then we stop, since we're too big
        } else {
            if (rightChild > size-1){ // If the right isn't in bounds, just consider the left child
                if(heap[index].comparePrice(heap[leftChild])){
                    // Our index is higher priority
                } else { // Our child is higher priority
                    heap[index].pSwap(heap[leftChild]); // First swap index values
                    CarNode temp = heap[index];
                    heap[index] = heap[leftChild];
                    heap[leftChild] = temp;             // Then swap in the array
                    index = leftChild;
                    // And recurse
                    sink(car, index);
                }
            } else {
                // Determine the smaller child
                if(heap[leftChild].comparePrice(heap[rightChild])){ // Left is smaller
                    if(heap[index].comparePrice(heap[leftChild])){
                        // Our index is higher priority
                    } else { // Our child is higher priority
                        heap[index].pSwap(heap[leftChild]); // First swap index values
                        CarNode temp = heap[index];
                        heap[index] = heap[leftChild];
                        heap[leftChild] = temp;             // Then swap in the array
                        index = leftChild;
                        // And recurse
                        sink(car, index);
                    }
                } else { // Right is smaller
                    if(heap[index].comparePrice(heap[rightChild])){
                        // Our index is higher priority
                    } else { // Our child is higher priority
                        heap[index].pSwap(heap[rightChild]); // First swap index values
                        CarNode temp = heap[index];
                        heap[index] = heap[rightChild];
                        heap[rightChild] = temp;             // Then swap in the array
                        index = rightChild;
                        // And recurse
                        sink(car, index);
                    }
                }
            }
        }
    }
}

/* A heap used to keep track of the minimum mileage of cars
 */
class MinMileHeap extends BaseHeap
{
    /* Add a car to the heap by creating a new node, adding it
    * to the heap, and rearranging the nodes
    */
    public void addCar(CarNode car)
    {
        if(size == (heap.length-1)){ // Resize
            resize();
        }
        // Add the car to the first available spot
        heap[size] = car;
        car.setmIndex(size);
        // Arrange new car within the heap
        swim(heap[size], size);

        size += 1;  // Increase size
    }

    /* Update a car's position in the heap after making modifications to it
     * using the update option from the menu
     */
    public void updateCar(CarNode car)
    {
        // Try swimming
        swim(car, car.mIndex);
        // Try sinking
        sink(car, car.mIndex);
    }

    /* Delete a car from the heap
     */
    public void deleteCar(CarNode car)
    {
        // Determine the car's index.
        int dCarIndex = car.mIndex;
        // If it's the last or only option, just get rid of it
        if(dCarIndex == (size-1))
        {
            heap[size-1] = null;
            size -=1;   // Decrease size
        } else {
            // The final car is at heap[size-1]. Update the index
            heap[size-1].mIndex = dCarIndex;
            // Use the Node to replace the old car
            heap[dCarIndex] = heap[size-1];
            // Remove the previous reference
            heap[size-1] = null;
            size -=1;

            // Determine the updated place of the replacement car
            swim(heap[dCarIndex], dCarIndex);
            sink(heap[dCarIndex], dCarIndex);
        }
    }

    public void swim(CarNode car, int index)
    {
        // Have we reached the top?
        if(index == 0) return;
        // If the parent is lower priority, switch
        int parIndex = (int) Math.floor((index-1)/2);
        if(car.compareMiles(heap[parIndex])){
            // Swap the two cars
            car.mSwap(heap[parIndex]);
            CarNode temp = heap[parIndex];
            heap[parIndex] = car;
            heap[index] = temp;
            index = parIndex;  // Switch index
            // And recurse
            swim(car, index);
        }
        // Otherwise, we're at the proper priority
    }

    public void sink(CarNode car, int index)
    {
        // Indicies of the children
        int leftChild = (2*index) + 1;
        int rightChild = (2*index) + 2;
        // Are the left and right child within bounds?
        if(leftChild > size-1){
            // Then we stop, since we're too big
        } else {
            if (rightChild > size-1){ // If the right isn't in bounds, just consider the left child
                if(heap[index].compareMiles(heap[leftChild])){
                    // Our index is higher priority
                } else { // Our child is higher priority
                    heap[index].mSwap(heap[leftChild]); // First swap index values
                    CarNode temp = heap[index];
                    heap[index] = heap[leftChild];
                    heap[leftChild] = temp;             // Then swap in the array
                    index = leftChild;
                    // And recurse
                    sink(car, index);
                }
            } else {
                // Determine the smaller child
                if(heap[leftChild].compareMiles(heap[rightChild])){ // Left is smaller
                    if(heap[index].compareMiles(heap[leftChild])){
                        // Our index is higher priority
                    } else { // Our child is higher priority
                        heap[index].mSwap(heap[leftChild]); // First swap index values
                        CarNode temp = heap[index];
                        heap[index] = heap[leftChild];
                        heap[leftChild] = temp;             // Then swap in the array
                        index = leftChild;
                        // And recurse
                        sink(car, index);
                    }
                } else { // Right is smaller
                    if(heap[index].compareMiles(heap[rightChild])){
                        // Our index is higher priority
                    } else { // Our child is higher priority
                        heap[index].mSwap(heap[rightChild]); // First swap index values
                        CarNode temp = heap[index];
                        heap[index] = heap[rightChild];
                        heap[rightChild] = temp;             // Then swap in the array
                        index = rightChild;
                        // And recurse
                        sink(car, index);
                    }
                }
            }
        }
    }
}

class mmMinPriceHeap extends BaseHeap
{
    /* Add a car to the heap by creating a new node, adding it
     * to the heap, and rearranging the nodes
     */
    public void addCar(CarNode car)
    {
        if(size == (heap.length-1)){ // Resize
            resize();
        }
        // Add the car to the first available spot
        heap[size] = car;
        car.setmpIndex(size);
        // Arrange new car within the heap
        swim(heap[size], size);

        size += 1;  // Increase size
    }

    /* Update a car's position in the heap after making modifications to it
     * using the update option from the menu
     */
    public void updateCar(CarNode car)
    {
        // Try swimming
        swim(car, car.mpIndex);
        // Try sinking
        sink(car, car.mpIndex);
    }

    /* Delete a car from the heap
     */
    public void deleteCar(CarNode car)
    {
        // Determine the car's index.
        int dCarIndex = car.mpIndex;
        // If the index is the same as size-1, we can just make it null
        if(dCarIndex == (size-1))
        {
            heap[size-1] = null;
            size -=1;   // Decrease size
        } else {
            // The final car is at heap[size-1]. Update the index
            heap[size-1].mpIndex = dCarIndex;
            // Use the Node to replace the old car
            heap[dCarIndex] = heap[size-1];
            // Remove the previous reference
            heap[size-1] = null;
            size -=1;

            // Determine the updated place of the replacement car
            swim(heap[dCarIndex], dCarIndex);
            sink(heap[dCarIndex], dCarIndex);
        }
    }

    public void swim(CarNode car, int index)
    {
        // Have we reached the top?
        if(index == 0) return;
        // If the parent is lower priority, switch
        int parIndex = (int) Math.floor((index-1)/2);
        if(car.comparePrice(heap[parIndex])){
            // Swap the two cars
            car.mpSwap(heap[parIndex]);
            CarNode temp = heap[parIndex];
            heap[parIndex] = car;
            heap[index] = temp;
            index = parIndex;  // Switch index
            // And recurse
            swim(car, index);
        }
        // Otherwise, we're at the proper priority
    }

    public void sink(CarNode car, int index)
    {
        // Indicies of the children
        int leftChild = (2*index) + 1;
        int rightChild = (2*index) + 2;
        // Are the left and right child within bounds?
        if(leftChild > size-1){
            // Then we stop, since we're too big
        } else {
            if (rightChild > size-1){ // If the right isn't in bounds, just consider the left child
                if(heap[index].comparePrice(heap[leftChild])){
                    // Our index is higher priority
                } else { // Our child is higher priority
                    heap[index].mpSwap(heap[leftChild]); // First swap index values
                    CarNode temp = heap[index];
                    heap[index] = heap[leftChild];
                    heap[leftChild] = temp;             // Then swap in the array
                    index = leftChild;
                    // And recurse
                    sink(car, index);
                }
            } else {
                // Determine the smaller child
                if(heap[leftChild].comparePrice(heap[rightChild])){ // Left is smaller
                    if(heap[index].comparePrice(heap[leftChild])){
                        // Our index is higher priority
                    } else { // Our child is higher priority
                        heap[index].mpSwap(heap[leftChild]); // First swap index values
                        CarNode temp = heap[index];
                        heap[index] = heap[leftChild];
                        heap[leftChild] = temp;             // Then swap in the array
                        index = leftChild;
                        // And recurse
                        sink(car, index);
                    }
                } else { // Right is smaller
                    if(heap[index].comparePrice(heap[rightChild])){
                        // Our index is higher priority
                    } else { // Our child is higher priority
                        heap[index].mpSwap(heap[rightChild]); // First swap index values
                        CarNode temp = heap[index];
                        heap[index] = heap[rightChild];
                        heap[rightChild] = temp;             // Then swap in the array
                        index = rightChild;
                        // And recurse
                        sink(car, index);
                    }
                }
            }
        }
    }
}

class mmMinMileHeap extends BaseHeap
{
    /* Add a car to the heap by creating a new node, adding it
     * to the heap, and rearranging the nodes
     */
    public void addCar(CarNode car)
    {
        if(size == (heap.length-1)){ // Resize
            resize();
        }
        // Add the car to the first available spot
        heap[size] = car;
        car.setmmIndex(size);
        // Arrange new car within the heap
        swim(heap[size], size);

        size += 1;  // Increase size
    }

    /* Update a car's position in the heap after making modifications to it
     * using the update option from the menu
     */
    public void updateCar(CarNode car)
    {
        // Try swimming
        swim(car, car.mmIndex);
        // Try sinking
        sink(car, car.mmIndex);
    }

    /* Delete a car from the heap
     */
    public void deleteCar(CarNode car)
    {
        // Determine the car's index.
        int dCarIndex = car.mmIndex;
        // If the index is the same as size-1, we can just make it null
        if(dCarIndex == (size-1))
        {
            heap[size-1] = null;
            size -=1;   // Decrease size
        } else {
            // The final car is at heap[size-1]. Update the index
            heap[size-1].mmIndex = dCarIndex;
            // Use the Node to replace the old car
            heap[dCarIndex] = heap[size-1];
            // Remove the previous reference
            heap[size-1] = null;
            size -=1;   // Decrease size

            // Determine the updated place of the replacement car
            swim(heap[dCarIndex], dCarIndex);
            sink(heap[dCarIndex], dCarIndex);
        }
    }

    public void swim(CarNode car, int index)
    {
        // Have we reached the top?
        if(index == 0) return;
        // If the parent is lower priority, switch
        int parIndex = (int) Math.floor((index-1)/2);
        if(car.compareMiles(heap[parIndex])){
            // Swap the two cars
            car.mmSwap(heap[parIndex]);
            CarNode temp = heap[parIndex];
            heap[parIndex] = car;
            heap[index] = temp;
            index = parIndex;  // Switch index
            // And recurse
            swim(car, index);
        }
        // Otherwise, we're at the proper priority
    }

    public void sink(CarNode car, int index)
    {
        // Indicies of the children
        int leftChild = (2*index) + 1;
        int rightChild = (2*index) + 2;
        // Are the left and right child within bounds?
        if(leftChild > size-1){
            // Then we stop, since we're too big
        } else {
            if (rightChild > size-1){ // If the right isn't in bounds, just consider the left child
                if(heap[index].compareMiles(heap[leftChild])){
                    // Our index is higher priority
                } else { // Our child is higher priority
                    heap[index].mmSwap(heap[leftChild]); // First swap index values
                    CarNode temp = heap[index];
                    heap[index] = heap[leftChild];
                    heap[leftChild] = temp;             // Then swap in the array
                    index = leftChild;
                    // And recurse
                    sink(car, index);
                }
            } else {
                // Determine the smaller child
                if(heap[leftChild].compareMiles(heap[rightChild])){ // Left is smaller
                    if(heap[index].compareMiles(heap[leftChild])){
                        // Our index is higher priority
                    } else { // Our child is higher priority
                        heap[index].mmSwap(heap[leftChild]); // First swap index values
                        CarNode temp = heap[index];
                        heap[index] = heap[leftChild];
                        heap[leftChild] = temp;             // Then swap in the array
                        index = leftChild;
                        // And recurse
                        sink(car, index);
                    }
                } else { // Right is smaller
                    if(heap[index].compareMiles(heap[rightChild])){
                        // Our index is higher priority
                    } else { // Our child is higher priority
                        heap[index].mmSwap(heap[rightChild]); // First swap index values
                        CarNode temp = heap[index];
                        heap[index] = heap[rightChild];
                        heap[rightChild] = temp;             // Then swap in the array
                        index = rightChild;
                        // And recurse
                        sink(car, index);
                    }
                }
            }
        }
    }
}

/* A class used to hold other heaps for the min-mile hash table
 * It will be used as a value, and the make/model hash will be used as
 * a key.
 */
class HeapHolder
{
    mmMinPriceHeap makemodPrice;
    mmMinMileHeap makemodMile;

    public HeapHolder()
    {
        makemodPrice = new mmMinPriceHeap();
        makemodMile = new mmMinMileHeap();
    }

    // Two get functions
    public mmMinPriceHeap getmmP()
    {
        return makemodPrice;
    }
    public mmMinMileHeap getmmM()
    {
        return makemodMile;
    }

    // Add function
    public void addToHeaps(CarNode car)
    {
        makemodPrice.addCar(car);
        makemodMile.addCar(car);
    }
    // Update function
    public void updateHeaps(CarNode car)
    {
        makemodPrice.updateCar(car);
        makemodMile.updateCar(car);
    }
    // Delete function
    public void deleteFromHeaps(CarNode car)
    {
        makemodPrice.deleteCar(car);
        makemodMile.deleteCar(car);
    }
    // Get lowest price
    public CarNode getLowestPrice()
    {
        return makemodPrice.lowest();
    }
    // Get lowest mileage
    public CarNode getLowestMile()
    {
        return makemodMile.lowest();
    }
}

/* A special node for holding car information
 */
class CarNode
{
    // Car info
    String vin;
    String make;
    String model;
    int price;
    int mileage;
    String color;
    // Car indicies
    int pIndex; // Price
    int mIndex; // Mileage
    int mpIndex; 
    int mmIndex; // Make and model heap indicies

    // Create the car node
    public CarNode(String pVin, String pMake, String pModel, int pPrice,
                    int pMileage, String pColor)
    {
        vin = pVin; make = pMake; model = pModel; 
        price = pPrice; mileage = pMileage; color = pColor;
        pIndex = -2; mIndex = -2; mpIndex = -2; mmIndex = -2;
    }

    public void setpIndex(int i)
    {
        pIndex = i;
    }
    public void setmIndex(int i)
    {
        mIndex = i;
    }
    public void setmpIndex(int i)
    {
        mpIndex = i;
    }
    public void setmmIndex(int i)
    {
        mmIndex = i;
    }

    // 3 methods that each swap a different index between 2 CarNodes
    public void pSwap(CarNode other)
    {
        int temp = this.pIndex;
        this.pIndex = other.pIndex;
        other.pIndex = temp;
    }

    public void mSwap(CarNode other)
    {
        int temp = this.mIndex;
        this.mIndex = other.mIndex;
        other.mIndex = temp;
    }

    public void mpSwap(CarNode other)
    {
        int temp = this.mpIndex;
        this.mpIndex = other.mpIndex;
        other.mpIndex = temp;
    }

    public void mmSwap(CarNode other)
    {
        int temp = this.mmIndex;
        this.mmIndex = other.mmIndex;
        other.mmIndex = temp;
    }

    /* Methods that compare this node's price to another node, and
     * return true if this node is of higher priority
     */
    public boolean comparePrice(CarNode other)
    {
        // If this node is lower, return it
        if(this.price < other.price) return true;
        // Else return the other one
        return false;
    }

    public boolean compareMiles(CarNode other)
    {
        // If this node is lower, return it
        if(this.mileage < other.mileage) return true;
        // Else return the other one
        return false;
    }

    public void display()
    {
        System.out.println("\nVIN: " + this.vin +
                            "\nMake: " + this.make +
                            "\nModel: " + this.model +
                            "\nPrice: " + this.price +
                            "\nMileage: " + this.mileage +
                            "\nColor: " + this.color);
    }
}